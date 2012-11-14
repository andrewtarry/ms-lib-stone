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

namespace DataSift\Stone\TimeLib;

/**
 * Helper class for working with times
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class DateInterval extends \DateInterval
{
	public function getTotalMinutes()
	{
		// get total days
		$days = $this->format('%a');

		return ($days * 24 * 60) + ($this->h * 60) + ($this->i);
	}

	public function getTotalSeconds()
	{
		// get total days
		return ($this->y * 365 * 24 * 60 * 60) +
               ($this->m * 30 * 24 * 60 * 60) +
               ($this->d * 24 * 60 * 60) +
               ($this->h * 60 *60) +
                $this->s;
	}
}