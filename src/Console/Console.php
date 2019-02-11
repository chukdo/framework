<?php namespace Chukdo\Console;

class Console
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $border = true;

    /**
     * @var bool
     */
    private $allBorders = false;

    /**
     * @var int
     */
    private $padding = 1;

    /**
     * @var int
     */
    private $indent = 0;

    /**
     * @var int
     */
    private $rowIndex = -1;

    /**
     * @var array
     */
    private $columnWidths = [];

    /**
     * @var array
     */
    private $foregroundColors = [
        'white' => '1;37',
        'black' => '0;30',
        'red'   => '0;31',
        'green' => '0;32',
        'yellow'=> '1;33',
        'blue'  => '0;34',
        'grey'  => '0;37'
    ];

    /**
     * @var array
     */
    private $backgroundColors = [
        'white' => '',
        'black' => '40',
        'red'   => '41',
        'green' => '42',
        'yellow'=> '43',
        'blue'  => '44',
        'grey'  => '47'
    ];

    /**
     * Console constructor.
     */
    public function __construct()
    {
        $this->data         = [];
        $this->columnWidths = [];
        $this->rowIndex     = -1;
        $this->border       = true;
        $this->allBorders   = false;
        $this->padding      = 1;
        $this->indent       = 0;
    }

    /**
     * @param string $data
     * @param string|null $foregroundColor
     * @return string
     */
    public function color(string $data, string $foregroundColor = null): string
    {
        return "\033[" . $this->foregroundColors[$foregroundColor] . "m" . $data . "\033[0m";
    }

    /**
     * @param string $data
     * @param string|null $backgroundColor
     * @return string
     */
    public function background(string $data, string $backgroundColor = null): string
    {
        return "\033[" . $this->backgroundColors[$backgroundColor] . "m" . $data . "\033[0m";
    }

    /**
     * @param string $header
     * @param int|null $strPad
     * @return Console
     */
    public function addHeader(string $header, int $strPad = null): self
    {
        $this->data[-1][] = $strPad > strlen($header) ? str_pad($header, $strPad) : $header;

        return $this;
    }

    /**
     * @param array $headers
     * @return Console
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $size => $header) {
            $this->addHeader($header, $size);
        }

        return $this;
    }

    /**
     * @param array|null $data
     * @return Console
     */
    public function addRow($data = null): self
    {
        $this->rowIndex++;

        foreach ((array) $data as $col => $content) {
            $this->data[$this->rowIndex][$col] = $content;
        }

        return $this;
    }

    /**
     * @param $content
     * @param null $col
     * @param null $row
     * @return Console
     */
    public function addColumn($content, $col = null, $row = null): self
    {
        $row = $row === null ? $this->rowIndex : $row;
        if ($col === null) {
            $col = isset($this->data[$row]) ? count($this->data[$row]) : 0;
        }

        $this->data[$row][$col] = $content;

        return $this;
    }

    /**
     * @return Console
     */
    public function showBorder(): self
    {
        $this->border = true;

        return $this;
    }

    /**
     * @return Console
     */
    public function hideBorder(): self
    {
        $this->border = false;

        return $this;
    }

    /**
     * @return Console
     */
    public function showAllBorders(): self
    {
        $this->showBorder();
        $this->allBorders = true;

        return $this;
    }

    /**
     * @param int $value
     * @return Console
     */
    public function setPadding(int $value = 1): self
    {
        $this->padding = $value;

        return $this;
    }

    /**
     * @param int $value
     * @return Console
     */
    public function setIndent(int $value = 0): self
    {
        $this->indent = $value;

        return $this;
    }

    /**
     * @return Console
     */
    public function addBorderLine(): self
    {
        $this->rowIndex++;
        $this->data[$this->rowIndex] = 'HR';

        return $this;
    }

    /**
     * @return Console
     */
    public function display(): self
    {
        echo $this->getTable();

        return $this;
    }

    /**
     * @return Console
     */
    public function flush(): self
    {
        $this->display();
        $this->data         = [];
        $this->rowIndex     = -1;

        return $this;
    }

    /**
     * @return Console
     */
    public function flushAll(): self
    {
        $this->display();
        $this->__construct();

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        $this->computeColumnWidths();

        $endBorder  = false;
        $output     = $this->border ? $this->getBorder() : '';

        foreach ($this->data as $y => $row) {
            if ($row === 'HR') {
                if (!$this->allBorders) {
                    $output   .= $this->getBorder();
                    $endBorder = true;

                    unset($this->data[$y]);
                }

                continue;
            }

            foreach ($row as $x => $cell) {
                $output   .= $this->getCell($x, $row);
                $endBorder = false;
            }
            $output .= PHP_EOL;

            if ($y === -1) {
                $output   .= $this->getBorder();
                $endBorder = true;
            } else {
                if ($this->allBorders) {
                    $output   .= $this->getBorder();
                    $endBorder = true;
                }
            }
        }

        if (!$this->allBorders && !$endBorder) {
            $output .= $this->border ? $this->getBorder() : '';
        }

        return $output;
    }

    /**
     * @return string
     */
    private function getBorder(): string
    {
        $output = '';

        if (isset($this->data[0])) {
            $columnCount = count($this->data[0]);
        } elseif (isset($this->data[-1])) {
            $columnCount = count($this->data[-1]);
        } else {
            return $output;
        }

        for ($col = 0; $col < $columnCount; $col++) {
            $output .= $this->getCell($col);
        }

        if ($this->border) {
            $output .= '+';
        }
        $output .= PHP_EOL;

        return $output;
    }

    /**
     * @param int $index
     * @param array|null $row
     * @return string
     */
    private function getCell(int $index, array $row = null): string
    {
        $cell       = $row ? $row[$index] : '-';
        $width      = $this->columnWidths[$index];
        $padding    = str_repeat($row ? ' ' : '-', $this->padding);

        $output = '';

        if ($index === 0) {
            $output .= str_repeat(' ', $this->indent);
        }

        if ($this->border) {
            $output .= $row ? '|' : '+';
        }

        $output .= $padding;
        $cell    = trim(preg_replace('/\s+/', ' ', $cell));
        $content = preg_replace('#\x1b[[][^A-Za-z]*[A-Za-z]#', '', $cell);
        $delta   = strlen($cell) - strlen($content);
        $output .= str_pad($cell, $width + $delta, $row ? ' ' : '-');
        $output .= $padding;

        if ($row && $index == count($row) - 1 && $this->border) {
            $output .= $row ? '|' : '+';
        }

        return $output;
    }

    /**
     * @return array
     */
    private function computeColumnWidths(): array
    {
        foreach ($this->data as $y => $row) {
            if (is_array($row)) {
                foreach ($row as $x => $col) {
                    $content = preg_replace('#\x1b[[][^A-Za-z]*[A-Za-z]#', '', $col);

                    if (!isset($this->columnWidths[$x])) {
                        $this->columnWidths[$x] = strlen($content);

                    } else if (strlen($content) > $this->columnWidths[$x]) {
                        $this->columnWidths[$x] = strlen($content);
                    }
                }
            }
        }

        return $this->columnWidths;
    }
}