<?php

namespace Mrdatawolf\MultilineProgressbar;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProgressBar.
 */
class MultilineProgressbar
{
    /**
     * note: Each of the lines are held on their own here.
     *
     * @var ProgressBar
     */
    protected $progressBar, $progressBar2, $progressBar3, $messageBar;

    /**
     * note: These hold the total we are expecting for each progressbar. Message bar uses the sum total of the 3 maxs.
     *
     * @var int|null
     */
    protected $max, $max2, $max3;

    /**
     * note: These tell us how often add to each progressbars.
     *
     * @var int
     */
    protected $percentInc, $percentInc2, $percentInc3;

    /**
     * note: How many lines are we working with.
     *
     * @var int
     */
    protected $totalLines;

    /**
     * note: Which item we are on for each progressbar.
     *
     * @var int|null
     */
    protected $progressValue, $progressValue2, $progressValue3;

    /**
     * note: The message we display on line. $message and $secondary are on the same line by default.
     *
     * @var string
     */
    protected $message, $secondary, $progress, $progress2, $progress3;

    /**
     * note: The formating for the top line.  This is the
     *
     * @var string
     */
    protected $messageFormat = '%message% -> %secondary% : %elapsed:6s%/%estimated:-6s% %memory:6s%';

    /**
     * note: All the progressbars share the same formatign and it's set here.
     *
     * @var string
     */
    protected $barFormat = '%message% : %current%/%max% [%bar%] %percent:3s%% ';

    /**
     * If false we use \ | / and -.  If true we stop using - as it can cause the message line to wobble.
     *
     * @var bool
     */
    protected $removeWobble = false;

    /**
     * note: We display back into the output object which is passed to us.
     *
     * @var OutputInterface
     */
    public $output;


    /**
     * EnhancedProgressBar constructor.
     *
     * note: this lets us have progress bars with up to 3 lines of progress.
     *
     * @param OutputInterface $output
     * @param string          $message
     * @param string          $secondary
     * @param int             $max
     * @param int|null        $max2
     * @param int|null        $max3
     * @param int             $percentInc
     * @param int             $percentInc2
     * @param int             $percentInc3
     */
    public function __construct(
        $output,
        $message,
        $secondary,
        $max = 0,
        $max2 = null,
        $max3 = null,
        $percentInc = 2,
        $percentInc2 = 2,
        $percentInc3 = 2
    ) {
        $this->output      = $output;
        $this->message     = $message;
        $this->secondary   = $secondary;
        $this->max         = $max;
        $this->max2        = $this->max * $max2;
        $this->max3        = $this->max2 * $max3;
        $this->percentInc  = $percentInc;
        $this->percentInc2 = $percentInc2;
        $this->percentInc3 = $percentInc3;
        $this->totalLines  = $this->getTotalLineCount();
    }


    /**
     * note: Builds the progress bars.
     *
     * @param $type
     * @param $messages
     * @param $max
     * @param $percentInc
     */
    protected function buildProgressBar($type, $messages, $max, $percentInc)
    {
        if ( ! is_array($messages)) {
            $messages = ['message' => $messages];
        }
        $typeBar        = $this->barType($type);
        $this->$typeBar = new ProgressBar($this->output, $max);
        $this->$typeBar->setFormatDefinition('lglMessage', $this->messageFormat);
        $this->$typeBar->setFormatDefinition('lglBar', $this->barFormat);
        switch ($type) {
            case 'message':
                $this->$typeBar->setFormat('lglMessage');
                break;
            default :
                $this->$typeBar->setFormat('lglBar');
        }

        $percentNumber = $percentInc / 100;
        $redrawFreq    = ($max < 100 || $percentInc === 0) ? 1 : ceil($max * $percentNumber);
        $this->$typeBar->setRedrawFrequency($redrawFreq);

        foreach ($messages as $key => $value) {
            $this->setMessage($value, $key);
        }

        $this->$typeBar->start();
    }


    /**
     * note: Deals with moving the cursor up.
     *
     * @param $type
     */
    protected function lineUpCursor($type)
    {
        $lines      = $this->lineCount($type);
        $totalLines = $this->totalLines - $lines;
        for ($i = 0; $i < $totalLines; $i++) {
            print "\033[1A";
        }
    }


    /**
     * note: Deals with moving the cursor down.
     *
     * @param $type
     */
    protected function lineDownCursor($type)
    {
        $lines      = $this->lineCount($type);
        $totalLines = $this->totalLines - $lines;
        for ($i = 0; $i < $totalLines; $i++) {
            print PHP_EOL;
        }
    }


    /**
     * note: Lets us know how many lines we will be moving thru.
     *
     * @param $type
     *
     * @return int
     */
    protected function lineCount($type)
    {
        $line = 0;
        switch ($type) {
            case 'progress' :
                $line = 1;
                break;
            case 'progress2' :
                $line = 2;
                break;
            case 'progress3' :
                $line = 3;
                break;
        }

        return $line;
    }


    /**
     * @param $type
     *
     * @return string
     */
    protected function barType($type)
    {
        switch ($type) {
            case 'progress' :
                $barType = 'progressBar';
                break;
            case 'progress2' :
                $barType = 'progressBar2';
                break;
            case 'progress3' :
                $barType = 'progressBar3';
                break;
            default :
                $barType = 'messageBar';
        }

        return $barType;
    }

    /**
     * @return int
     */
    protected function getTotalLineCount()
    {
        $count = 1;
        foreach (['max', 'max2', 'max3'] as $check) {
            if ( ! empty($this->$check)) {
                $count++;
            }
        }

        return $count;
    }


    /**
     * note: If this is used we always update with each atomic change.  Normally we update when the percent ticks for a
     * line.
     *
     * @return $this
     */
    public function debug()
    {
        $this->percentInc  = 0;
        $this->percentInc2 = 0;
        $this->percentInc3 = 0;

        return $this;
    }


    /**
     * note: If this is used we remove the - character on the spinner.
     */
    public function removeSpinWobble()
    {
        $this->removeWobble = true;
    }


    /**
     * note: This starts the displaying of the progress bar(s).
     */
    public function start()
    {
        $thisMessage   = (empty($this->message)) ? 'message' : $this->message;
        $thisMessage2  = (empty($this->secondary)) ? 'secondary' : $this->secondary;
        $messagesCount = max([$this->max, $this->max2, $this->max3]);
        $this->buildProgressBar('message', ['message' => $thisMessage, 'secondary' => $thisMessage2], $messagesCount,
            0);
        print PHP_EOL;
        $thisMessage = (empty($this->progress)) ? 'progress' : $this->progress;
        $this->buildProgressBar('progress', ['progress' => $thisMessage], $this->max, $this->percentInc);
        print PHP_EOL;

        if ( ! empty($this->max2)) {
            $thisMessage = (empty($this->progress2)) ? 'progress2' : $this->progress2;
            $this->buildProgressBar('progress2', ['progress2' => $thisMessage], $this->max2, $this->percentInc2);
            print PHP_EOL;
        }
        if ( ! empty($this->max3)) {
            $thisMessage = (empty($this->progress3)) ? 'progress3' : $this->progress3;
            $this->buildProgressBar('progress3', ['progress3' => $thisMessage], $this->max3, $this->percentInc3);
            print PHP_EOL;
        }
    }


    /**
     * note: use this when the only lines you want to display are the messagebar lines.
     *
     * @param string $message
     * @param string $secondary
     */
    public function messageBarStart($message, $secondary = '|')
    {
        $this->message    = $message;
        $this->secondary  = $secondary;
        $this->percentInc = 0;
        $this->buildProgressBar('message', ['message' => $message, 'secondary' => $secondary], 1, 0);
    }


    /**
     * note: Moves the cursor around based on the bar we are attempting to manipulate and runs the advance for the
     * progressbar(s) we are working on.
     *
     * @param $types
     */
    public function advance($types)
    {
        $typeArray = (is_array($types)) ? $types : [$types];

        foreach ($typeArray as $type) {
            $this->lineUpCursor($type);
            $barType = $this->barType($type);
            $this->$barType->advance();
            $this->lineDownCursor($type);
        }
    }


    /**
     * note: Use when we are done.
     *
     * @return string
     */
    public function finish()
    {
        $this->lineUpCursor('message');
        $this->messageBar->finish();
        print PHP_EOL;
        $return = 'message';

        if ( ! empty($this->progressBar)) {
            $this->progressBar->finish();
            print PHP_EOL;
            $return = 'progress';
        }
        if ( ! empty($this->progressBar2)) {
            $this->progressBar2->finish();
            print PHP_EOL;
            $return = 'progress2';
        }
        if ( ! empty($this->progressBar3)) {
            $this->progressBar3->finish();
            print PHP_EOL;
            $return = 'progress3';
        }
        print PHP_EOL;
        print PHP_EOL;

        return $return;
    }


    /**
     * note: Lets us update the various user supplied strings.
     *
     * @param      $message
     * @param null $type
     */
    public function setMessage($message, $type = null)
    {
        if (empty($type)) {
            $this->message = $message;
            $this->messageBar->setMessage($message);
        } else {
            $barType     = $this->barType($type);
            $this->$type = $message;

            switch ($type) {
                case 'secondary' :
                    $this->$barType->setMessage($message, $type);
                    break;
                default :
                    $this->$barType->setMessage($message);
            }
        }
    }


    /**
     * note: Deals with updating the spinner string. If the spinner was on a different line we also correct for that
     * here.
     *
     * @param string $type
     */
    public function spin($type = 'secondary')
    {
        switch ($type) {
            case 'secondary':
                $spin = $this->secondary;
                break;
            case 'progress' :
                $spin = $this->progress;
                break;
            case 'progress2' :
                $spin = $this->progress2;
                break;
            case 'progress3' :
                $spin = $this->progress3;
                break;
            default :
                $spin = $this->message;
        }
        if ($this->removeWobble) {
            switch ($spin) {
                case '/':
                    $message = '\\';
                    break;
                case '\\' :
                    $message = '|';
                    break;
                default :
                    $message = '/';
            }
        } else {
            switch ($spin) {
                case '/':
                    $message = '-';
                    break;
                case '-':
                    $message = '\\';
                    break;
                case '\\' :
                    $message = '|';
                    break;
                default :
                    $message = '/';
            }
        }
        $this->setMessage($message, $type);
    }


    /**
     * note: shorthand for spin and advance.
     *
     * @param string $type
     */
    public function spinAdvance($type = 'secondary')
    {
        $this->spin($type);
        $this->advance($type);
    }


    /**
     * note: shorthand for setMessage, spin and advance.
     *
     * @param $message
     * @param $type
     */
    public function setMessageAndSpinAndAdvance($message, $type)
    {
        $types = [$type, 'secondary'];
        $this->setMessage($message, $type);
        $this->spin();
        $this->advance($types);
    }


    /**
     * @return int|null
     */
    public function getMax(): int
    {
        return $this->max;
    }


    /**
     * @return int|null
     */
    public function getMax2(): int
    {
        return $this->max2;
    }


    /**
     * @return int|null
     */
    public function getMax3(): int
    {
        return $this->max3;
    }


    /**
     * @return int|null
     */
    public function getPercentInc(): int
    {
        return $this->percentInc;
    }


    /**
     * @return int|null
     */
    public function getPercentInc2(): int
    {
        return $this->percentInc2;
    }


    /**
     * @return int|null
     */
    public function getPercentInc3(): int
    {
        return $this->percentInc3;
    }
}
