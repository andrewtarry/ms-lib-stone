<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   Stone/ComparisonLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\ComparisonLib;

/**
 * Support for comparing boolean values against other data
 *
 * @category  Libraries
 * @package   Stone/ComparisonLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class DoubleComparitor extends ComparitorBase
{
	// ==================================================================
	//
	// Helper methods
	//
	// ------------------------------------------------------------------

	/**
	 * is our test value really a boolean?
	 *
	 * @return ComparisonResult
	 */
	public function isExpectedType()
	{
		// our return object
		$result = new ComparisonResult();

		// is it _really_ a boolean?
		if (!is_double($this->value) && !is_float($this->value)) {
			$result->setHasFailed("double", gettype($this->value));
			return $result;
		}

		// if we get here, all is good
		$result->setHasPassed();

		// all done
		return $result;
	}

	// ==================================================================
	//
	// The comparisons that this data type supports
	//
	// ------------------------------------------------------------------

	// /**
	//  * test our data to see if it has the same value as another double
	//  *
	//  * @param  double $expected
	//  *         the value to compare against
	//  * @return ComparisonResult
	//  */
	// public function equals($expected)
	// {
	// 	// do we really have a double to test?
	// 	$result = $this->isExpectedType();
	// 	if ($result->hasFailed()) {
	// 		return $result;
	// 	}

	// 	// is our value the expected value?
	// 	if ($this->value != $expected) {
	// 		$result->setHasFailed("two values are equal", "two values are not equal");
	// 		return $result;
	// 	}

	// 	// success
	// 	return $result;
	// }

	/**
	 * is the value under test greater than what we expect?
	 *
	 * @param  double $expected
	 *         the value we expect to be greater than
	 * @return ComparisonResult
	 */
	public function isGreaterThan($expected)
	{
		// do we really have a double to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value greater than the expected value?
		if ($this->value <= $expected) {
			$result->setHasFailed("> {$expected}", "<= {$expected}");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test greater than or equal to what we expect?
	 *
	 * @param  double $expected
	 *         the value we expect to be greater than or equal to
	 * @return ComparisonResult
	 */
	public function isGreaterThanOrEqualTo($expected)
	{
		// do we really have a double to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value greater than the expected value?
		if ($this->value < $expected) {
			$result->setHasFailed(">= {$expected}", "< {$expected}");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test really a double?
	 * @return ComparisonResult
	 */
	public function isDouble()
	{
		return $this->isExpectedType();
	}

	/**
	 * is the value under test less than what we expect?
	 *
	 * @param  double $expected  the value we expect to be less than
	 * @return ComparisonResult
	 */
	public function isLessThan($expected)
	{
		// do we really have a double to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value less than the expected value?
		if ($this->value >= $expected) {
			$result->setHasFailed("< {$expected}", ">= {$expected}");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test less than or equal to what we expect?
	 *
	 * @param  double $expected
	 *         the value we expect to be less than or equal to
	 * @return ComparisonResult
	 */
	public function isLessThanOrEqualTo($expected)
	{
		// do we really have a double to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value less than or equal to the expected value?
		if ($this->value > $expected) {
			$result->setHasFailed("<= {$expected}", "> {$expected}");
			return $result;
		}

		// success
		return $result;
	}
}