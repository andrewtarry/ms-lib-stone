<?php

/**
 * Stone - A PHP Library
 *
 * PHP Version 5.3
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  Libraries
 * @package   Stone
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\HttpLib;

use DataSift\Stone\HttpLib\Transports\WsFrame;
use DataSift\Stone\LogLib\Log;

/**
 * Represents a response received from the HTTP server
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class HttpClientResponse
{
    const TYPE_INVALID   = false;
    const TYPE_IDENTITY  = 1;
    const TYPE_CHUNKED   = 2;
    const TYPE_WEBSOCKET = 3;

    /**
     * Did we receive a valid response from the remote HTTP server?
     *
     * If this is false, none of the other data can be trusted!!
     *
     * @var mixed
     *      false if the response is somehow invalid
     *      one of the self::TYPE_* constants if valid
     */
    public $type = false;

    /**
     * What version of HTTP does the remote HTTP server talk?
     *
     * This will normally be '1.0' or '1.1', as a string
     *
     * @var string
     */
    public $httpVersion;

    /**
     * What status code did the remote HTTP server send back?
     *
     * http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     *
     * @var string
     */
    public $statusCode = null;

    /**
     * What text string did the remote HTTP server send back to explain the
     * HTTP status code we received?
     *
     * @var string
     */

    public $statusMessage;

    /**
     * What headers came back in the response?
     *
     * @var array
     */
    public $headers = array();

    /**
     * What content came back from the remote HTTP server?
     *
     * We only set the body for non-chunked responses (e.g. page requests).
     *
     * @var string
     */
    public $body;

    /**
     * What content came back from the remote HTTP server?
     *
     * We only set the chunks for streamed responses (e.g. API requests)
     * @var array
     */

    public $chunks = array();

    /**
     * what frames came back from the remote HTTP server?
     *
     * We only set the frames when talking to websockets
     * @var array
     */

    public $frames = array();

    /**
     * How many bytes came back from the remote HTTP server?
     * @var int
     */
    public $bytesRead = 0;

    /**
     * What was the raw response we received from the remote HTTP server?
     * @var string
     */
    public $rawResponse = '';

    /**
     * How many times have we passed this response to HttpClientConnection::read()?
     *
     * @var int
     */
    public $responsesCount = 0;

    /**
     * A list of errors encountered trying to deal with the HTTP server
     *
     * @var array
     */
    public $errorMsgs = array();

    /**
     * do we need to disconnect?
     * @var boolean
     */
    public $mustClose = false;

    /**
     * What type of HTTP response are we representing?
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Parse a HTTP status line returned from the remote HTTP server?
     *
     * @param string $statusLine
     * @return boolean true if we could decode the status line, false otherwise
     */
    public function decodeStatusLine($statusLine)
    {
        if (substr($statusLine, 0, 4) !== 'HTTP')
        {
            // bad status line received
            $this->addError("decodeStatusLine", "server response does not start with 'HTTP'");
            $this->setType(self::TYPE_INVALID);
            return false;
        }

        $this->httpVersion = substr(strtok($statusLine, ' '), 5);
        $this->statusCode = strtok(' ');

        $append = false;
        while($reason = strtok(' '))
        {
            if ($append)
            {
                $this->statusMessage .= ' ';
            }
            $this->statusMessage .= $reason;
            $append = true;
        }

        // mark the request as valid
        //
        // we mark the response as being a plain old HTTP response
        // transports are free to change this if they know any better :)
        $this->setType(self::TYPE_IDENTITY);
        return true;
    }

    public function resetForNextResponse()
    {
        if ($this->type == self::TYPE_IDENTITY)
        {
            $this->resetHeaders();
        }

        // forget the data we saw last time
        $this->chunks    = array();
        $this->frames    = array();
        $this->errorMsgs = array();
        $this->body      = '';
        $this->bytesRead = 0;
    }

    /**
     * forget any headers we saw previously
     */
    protected function resetHeaders()
    {
        $this->statusCode = null;
        $this->statusMessage = null;
        $this->headers = array();
    }

    /**
     * Decode a single header line received from the remote HTTP server
     * @param string $headerLine
     * @return boolean true if successful, false otherwise
     */
    public function decodeHeader($headerLine)
    {
        $heading = strtok($headerLine, ':');
        if (!$heading)
        {
            // bad header
            $this->addError("decodeHeader", "header line invalid; line was: " . $headerLine);
            $this->setType(false);
            return false;
        }

        $value = substr($headerLine, strlen($heading) + 2);
        $this->headers[$heading] = $value;
    }

    /**
     * Decode a chunk of data received from the remote HTTP server
     *
     * @param string $chunk
     */
    public function decodeChunk($chunk)
    {
        // var_dump('>> decodeChunk()');
        // var_dump($chunk);
        $this->chunks[] = rtrim($chunk, "\r\n");
        Log::write(Log::LOG_DEBUG, end($this->chunks));
    }

    /**
     * Decode a websocket frame received from the remote HTTP server
     *
     * @param WsFrame $frame
     */
    public function decodeFrame(WsFrame $frame)
    {
        $this->frames[] = $frame;
        $this->chunks[] = $frame->getApplicationData();
    }

    /**
     * Does the connection to the remote HTTP server need to close?
     *
     * @return boolean true if it must close, false otherwise
     */
    public function connectionMustClose()
    {
        if ((isset($this->headers['Connection']) && $this->headers['Connection'] == 'close') || $this->mustClose)
        {
            return true;
        }

        return false;
    }

    /**
     * Is the remote HTTP server going to send us back chunks of data?
     *
     * @return boolean true if response will be in chunks, false if response will be one big splodge
     */
    public function transferIsChunked()
    {
        if (isset($this->headers['Transfer-Encoding']) && $this->headers['Transfer-Encoding'] == 'chunked')
        {
            $this->type = self::TYPE_CHUNKED;
            return true;
        }

        return false;
    }

    /**
     * Decode the content received from the remote HTTP server
     *
     * @param string $body
     */
    public function decodeBody($body)
    {
        $this->body = $body;
    }

    /**
     * Remember the raw data received from the remote HTTP server
     *
     * @param string $data data received from the remote HTTP server
     */
    public function addRawData($data)
    {
        $this->rawResponse .= $data;
    }

    /**
     * Increment how many times this specific response object has been used
     */
    public function incUsed()
    {
        $this->responsesCount++;
    }

    /**
     * Log an error that happened
     *
     * @param string $operation
     * @param string $message
     */
    public function addError($operation, $message)
    {
        $msg = "[" . date("Ymd-H:i:s") . "] $operation(): $message";
        $this->errorMsgs[] = $msg;
    }

    /**
     * Was there a problem getting the response?
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return (count($this->errorMsgs) > 0);
    }
}