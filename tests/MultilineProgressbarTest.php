<?php

namespace Mrdatawolf\MultilineProgressbar\Tests;

use PHPUnit\Framework\TestCase;
use Mrdatawolf\MultilineProgressbar\MultilineProgressbar;
use Mockery;

/**
 * Class MultilineProgressBarTest
 */
class MultilineProgressBarTest extends TestCase
{
    //BaseContentTestCase
    public $output;

    public function test_progressSetup()
    {
        $this->output = Mockery::mock('Symfony\Component\Console\Output\OutputInterface');
        $message   = 'message';
        $secondary = 'secondary';
        $max       = 1;
        $max2      = 2;
        $max3      = 3;
        $percent   = 4;
        $percent2  = 5;
        $percent3  = 6;
        $test   = new MultilineProgressBar($this->output, $message, $secondary, $max, $max2, $max3, $percent, $percent2, $percent3);
        $this->assertEquals($message, $test->message);
        $this->assertEquals($secondary, $test->secondary);
        $this->assertEquals($max, $test->max);
        $this->assertEquals($test->max*$test->max2, $test->max2);
        $this->assertEquals($test->max*$test->max2*$max3, $test->max3);
        $this->assertEquals($percent, $test->percentInc);
        $this->assertEquals($percent2, $test->percentInc2);
        $this->assertEquals($percent3, $test->percentInc3);
    }

    public function test_getLineCount_full()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 4, 5);
        $actual     = $test->getLineCount();
        $this->assertEquals(4, $actual);
    }

    public function test_getLineCount_2()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 5);
        $actual     = $test->getLineCount();
        $this->assertEquals(2, $actual);
    }

    public function test_lineCount_message()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 5);
        $actual = $test->lineCount('message');
        $this->assertEquals(0, $actual);
    }

    public function test_lineCount_secondary()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 5);
        $actual = $test->lineCount('secondary');
        $this->assertEquals(0, $actual);
    }
    public function test_lineCount_progress()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 5);
        $actual = $test->lineCount('progress');
        $this->assertEquals(1, $actual);
    }
    public function test_lineCount_progress2()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 5);
        $actual = $test->lineCount('progress2');
        $this->assertEquals(2, $actual);
    }
    public function test_lineCount_progress3()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 5);
        $actual = $test->lineCount('progress3');
        $this->assertEquals(3, $actual);
    }
}
