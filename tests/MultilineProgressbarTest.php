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
    public $output;


    public function test_progressSetup()
    {
        $this->output = Mockery::mock('Symfony\Component\Console\Output\OutputInterface');
        $message      = 'message';
        $secondary    = 'secondary';
        $max          = 1;
        $max2         = 2;
        $max3         = 3;
        $percent      = 4;
        $percent2     = 5;
        $percent3     = 6;
        $test         = new MultilineProgressBar($this->output, $message, $secondary, $max, $max2, $max3, $percent,
            $percent2, $percent3);
        $this->assertEquals($message, $test->getMessage());
        $this->assertEquals($secondary, $test->getSecondary());
        $this->assertEquals($max, $test->getMax());
        $this->assertEquals($max * $max2, $test->getMax2());
        $this->assertEquals($max * $max2 * $max3, $test->getMax3());
        $this->assertEquals($percent, $test->getPercentInc());
        $this->assertEquals($percent2, $test->getPercentInc2());
        $this->assertEquals($percent3, $test->getPercentInc3());
    }


    public function test_lineCount_message()
    {
        $test   = new MultilineProgressBar($this->output, 'test', 'stuff', 0, 0, 0);
        $actual = $test->getTotalLines();
        $this->assertEquals(1, $actual);
    }


    public function test_lineCount_secondary()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 0, 0, 0);
        $actual = $test->getTotalLines();
        $this->assertEquals(1, $actual);
    }


    public function test_lineCount_progress()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 0);
        $actual = $test->getTotalLines();
        $this->assertEquals(2, $actual);
    }

    public function test_lineCount_progress_ignore_third_when_second_empty()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 0, 5);
        $actual = $test->getTotalLines();
        $this->assertEquals(2, $actual);
    }


    public function test_lineCount_progress2()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 5);
        $actual = $test->getTotalLines();
        $this->assertEquals(3, $actual);
    }


    public function test_lineCount_progress3()
    {
        $test   = new MultilineProgressBar($this->output, 'test', '/', 1, 1, 5);
        $actual = $test->getTotalLines();
        $this->assertEquals(4, $actual);
    }
}
