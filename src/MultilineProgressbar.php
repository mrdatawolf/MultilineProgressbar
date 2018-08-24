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
     * @var ProgressBar
     */
    public $progressBar,$progressBar2,$progressBar3,$messageBar;

    /**
     * @var int|null
     */
    public $max, $max2, $max3, $percentInc, $percentInc2, $percentInc3;

    /**
     * @var int|null
     */
    protected $multiProgressLines, $progressValue, $progressValue2, $progressValue3;

    /**
     * @var string
     */
    public $message, $secondary, $progress, $progress2, $progress3;

    /**
     * @var string
     */
    public $messageFormat = '%message% -> %secondary% : %elapsed:6s%/%estimated:-6s% %memory:6s%';

    /**
     * @var string
     */
    public $barFormat = '%message% : %current%/%max% [%bar%] %percent:3s%% ';

    /**
     * @var bool
     */
    public $removeWobble = false;

    /**
     * @var OutputInterface
     */
    public $output;

    /**
     * EnhancedProgressBar constructor.
     *
     * note: this lets us have progress bars with up to 3 lines of progress.
     *
     * @param OutputInterface $output
     * @param string $message
     * @param string $secondary
     * @param int       $max
     * @param int|null  $max2
     * @param int|null  $max3
     * @param int       $percentInc
     * @param int       $percentInc2
     * @param int       $percentInc3
     */
    public function __construct($output, $message, $secondary, $max = 0, $max2 = null, $max3 = null, $percentInc = 2, $percentInc2 = 2, $percentInc3 = 2) {
        $this->output       = $output;
        $this->message      = $message;
        $this->secondary    = $secondary;
        $this->max          = $max;
        $this->max2         = $this->max * $max2;
        $this->max3         = $this->max2 * $max3;
        $this->percentInc   = $percentInc;
        $this->percentInc2  = $percentInc2;
        $this->percentInc3  = $percentInc3;
        $this->multiProgressLines = $this->getLineCount();
    }

    public function debug()
    {
        $this->percentInc  = 0;
        $this->percentInc2 = 0;
        $this->percentInc3 = 0;

        return $this;
    }

    public function removeSpinWobble()
    {
        $this->removeWobble = true;
    }

    public function start()
    {
        $thisMessage   = (empty($this->message)) ? 'message' : $this->message;
        $thisMessage2  = (empty($this->secondary)) ? 'secondary' : $this->secondary;
        $messagesCount = max([$this->max, $this->max2, $this->max3]);
        $this->buildProgressBar('message', ['message' => $thisMessage, 'secondary' => $thisMessage2], $messagesCount, 0);
        print PHP_EOL;
        $thisMessage = (empty($this->progress)) ? 'progress' : $this->progress;
        $this->buildProgressBar('progress', ['progress' => $thisMessage], $this->max, $this->percentInc);
        print PHP_EOL;

        if (! empty($this->max2)) {
            $thisMessage = (empty($this->progress2)) ? 'progress2' : $this->progress2;
            $this->buildProgressBar('progress2', ['progress2' => $thisMessage], $this->max2, $this->percentInc2);
            print PHP_EOL;
        }
        if (! empty($this->max3)) {
            $thisMessage = (empty($this->progress3)) ? 'progress3' : $this->progress3;
            $this->buildProgressBar('progress3', ['progress3' => $thisMessage], $this->max3, $this->percentInc3);
            print PHP_EOL;
        }
    }

    /**
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

    public function getCurrentValues()
    {
        if (! empty($this->progressBar)) {
            $this->progressValue = $this->progressBar->getProgress() - 1;
        }
        if (! empty($this->progressBar2)) {
            $this->progressValue2 = $this->progressBar2->getProgress() - 1;
        }
        if (! empty($this->progressBar3)) {
            $this->progressValue3 = $this->progressBar3->getProgress() - 1;
        }
    }

    public function jumpProgress()
    {
        if (! empty($this->progressBar)) {
            $this->progressBar->setProgress($this->progressValue);
            $this->advance('progress');
        }
        if (! empty($this->progressBar2)) {
            $this->progressBar2->setProgress($this->progressValue2);
            $this->advance('progress2');
        }
        if (! empty($this->progressBar3)) {
            $this->progressBar3->setProgress($this->progressValue3);
            $this->advance('progress3');
        }
    }

    /**
     * @param $type
     * @param $messages
     * @param $max
     * @param $percentInc
     */
    public function buildProgressBar($type, $messages, $max, $percentInc)
    {
        if (! is_array($messages)) {
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
        $redrawFreq =  ($max < 100 || $percentInc === 0) ? 1 : ceil($max * $percentNumber);
        $this->$typeBar->setRedrawFrequency($redrawFreq);

        foreach ($messages as $key => $value) {
            $this->setMessage($value, $key);
        }

        $this->$typeBar->start();
    }

    /**
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
     * @return string
     */
    public function finish()
    {
        $this->lineUpCursor('message');
        $this->messageBar->finish();
        print PHP_EOL;
        $return = 'message';

        if (! empty($this->progressBar)) {
            $this->progressBar->finish();
            print PHP_EOL;
            $return = 'progress';
        }
        if (! empty($this->progressBar2)) {
            $this->progressBar2->finish();
            print PHP_EOL;
            $return = 'progress2';
        }
        if (! empty($this->progressBar3)) {
            $this->progressBar3->finish();
            print PHP_EOL;
            $return = 'progress3';
        }
        print PHP_EOL;
        print PHP_EOL;

        return $return;
    }

    /**
     * @param $type
     */
    public function lineUpCursor($type)
    {
        $lines      = $this->lineCount($type);
        $totalLines = $this->multiProgressLines - $lines;
        for ($i = 0;$i < $totalLines;$i++) {
            print "\033[1A";
        }
    }

    /**
     * @param $type
     */
    public function lineDownCursor($type)
    {
        $lines      = $this->lineCount($type);
        $totalLines = $this->multiProgressLines - $lines;
        for ($i = 0;$i < $totalLines;$i++) {
            print PHP_EOL;
        }
    }

    /**
     * @return int
     */
    public function getLineCount()
    {
        $count = 1;
        foreach (['max', 'max2', 'max3'] as $check) {
            if (! empty($this->$check)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $type
     *
     * @return int
     */
    public function lineCount($type)
    {
        switch ($type) {
            case 'message' :
            case 'secondary' :
                $line = 0;
                break;
            case 'progress' :
                $line = 1;
                break;
            case 'progress2' :
                $line = 2;
                break;
            default :
                $line = 3;
        }

        return $line;
    }

    /**
     * @param      $message
     * @param null $type
     */
    public function setMessage($message, $type = null)
    {
        if (empty($type)) {
            $this->message = $message;
            $this->messageBar->setMessage($message);
        } else {
            $barType = $this->barType($type);
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
        if($this->removeWobble)
        {
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
        }
        else {
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
     * @param string $type
     */
    public function spinAdvance($type = 'secondary')
    {
        $this->spin($type);
        $this->advance($type);
    }

    /**
     * @param $message
     * @param $type
     */
    public function setMessageAndSpinAndAdvance($message, $type)
    {
        $types = [$type,'secondary'];
        $this->setMessage($message, $type);
        $this->spin();
        $this->advance($types);
    }

    /**
     * @param $type
     *
     * @return string
     */
    public function barType($type)
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
}
