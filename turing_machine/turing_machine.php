<?php

declare(strict_types=1);

/**
 * 無限テープを表すクラス。
 *
 * 空白記号は `_` を使う。
 */
class Tape
{
    /** @var array<int, string> */
    private array $cells = [];

    private string $blank;

    public function __construct(string $blank = '_')
    {
        $this->blank = $blank;
    }

    public function read(int $pos): string
    {
        return $this->cells[$pos] ?? $this->blank;
    }

    public function write(int $pos, string $symbol): void
    {
        if ($symbol === $this->blank) {
            unset($this->cells[$pos]);
            return;
        }

        $this->cells[$pos] = $symbol;
    }

    /**
     * ヘッド位置を含めて、見えている範囲を文字列化する。
     */
    public function toString(int $headPos): string
    {
        $positions = array_keys($this->cells);
        $positions[] = $headPos;
        $positions = array_values(array_unique($positions));
        sort($positions, SORT_NUMERIC);

        $parts = [];
        foreach ($positions as $pos) {
            $symbol = $this->read((int)$pos);
            if ((int)$pos === $headPos) {
                $parts[] = '[' . $symbol . ']';
                continue;
            }

            $parts[] = $symbol;
        }

        return implode(' ', $parts);
    }

    /**
     * テープ上の非空白部分を左から右へ連結する。
     */
    public function toWord(): string
    {
        if ($this->cells === []) {
            return '';
        }

        $positions = array_keys($this->cells);
        sort($positions, SORT_NUMERIC);

        $word = '';
        foreach ($positions as $pos) {
            $symbol = $this->cells[(int)$pos];
            if ($symbol === $this->blank) {
                continue;
            }
            $word .= $symbol;
        }

        return $word;
    }
}

/**
 * チューリングマシン本体。
 *
 * 7つ組 (Q, Γ, b, Σ, δ, q0, F) をそのまま受け取る。
 */
class TuringMachine
{
    /** @var array<int, string> */
    private array $states;

    /** @var array<int, string> */
    private array $alphabet;

    private string $blankSymbol;

    /** @var array<int, string> */
    private array $inputAlphabet;

    /** @var array<string, array<string, array{0: string, 1: string, 2: string}>> */
    private array $delta;

    private string $initialState;

    /** @var array<int, string> */
    private array $finalStates;

    public function __construct(
        array $states,
        array $alphabet,
        string $blankSymbol,
        array $inputAlphabet,
        array $delta,
        string $initialState,
        array $finalStates
    ) {
        $this->states = $states;
        $this->alphabet = $alphabet;
        $this->blankSymbol = $blankSymbol;
        $this->inputAlphabet = $inputAlphabet;
        $this->delta = $delta;
        $this->initialState = $initialState;
        $this->finalStates = $finalStates;
    }

    public function run(string $input, bool $trace = true, int $maxSteps = 10000): string
    {
        $this->validateInput($input);

        $tape = new Tape($this->blankSymbol);
        $chars = preg_split('//u', $input, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false) {
            throw new RuntimeException('入力を分解できませんでした。');
        }

        foreach ($chars as $index => $char) {
            $tape->write($index, $char);
        }

        $head = 0;
        $state = $this->initialState;
        $step = 0;

        if ($trace) {
            printf("step %2d  state=%s  tape=%s\n", $step, $state, $tape->toString($head));
        }

        while (true) {
            if (in_array($state, $this->finalStates, true)) {
                return $tape->toWord();
            }

            if ($step >= $maxSteps) {
                throw new RuntimeException('最大ステップ数を超えました。無限ループの可能性があります。');
            }

            $symbol = $tape->read($head);
            if (!isset($this->delta[$state][$symbol])) {
                throw new RuntimeException(sprintf('遷移が定義されていません: state=%s, symbol=%s', $state, $symbol));
            }

            [$writeSymbol, $move, $nextState] = $this->delta[$state][$symbol];
            $tape->write($head, $writeSymbol);

            if ($move === 'L') {
                $head--;
            } elseif ($move === 'R') {
                $head++;
            } elseif ($move !== 'N') {
                throw new RuntimeException(sprintf('不正な移動命令です: %s', $move));
            }

            $state = $nextState;
            $step++;

            if ($trace) {
                printf(
                    "step %2d  state=%s  read=%s write=%s move=%s next=%s  tape=%s\n",
                    $step,
                    $state,
                    $symbol,
                    $writeSymbol,
                    $move,
                    $nextState,
                    $tape->toString($head)
                );
            }
        }
    }

    private function validateInput(string $input): void
    {
        if ($input === '') {
            throw new InvalidArgumentException('入力は空にできません。');
        }

        if (!preg_match('/^[1+]+$/', $input)) {
            throw new InvalidArgumentException('入力は `1` と `+` だけで構成してください。');
        }

        if (substr_count($input, '+') !== 1) {
            throw new InvalidArgumentException('`+` はちょうど1個必要です。');
        }
    }
}

function buildUnaryAdditionMachine(): TuringMachine
{
    return new TuringMachine(
        ['q0', 'q1', 'q2', 'halt'],
        ['1', '+', '_'],
        '_',
        ['1', '+'],
        [
            'q0' => [
                '1' => ['1', 'R', 'q0'],
                '+' => ['1', 'R', 'q1'],
            ],
            'q1' => [
                '1' => ['1', 'R', 'q1'],
                '_' => ['_', 'L', 'q2'],
            ],
            'q2' => [
                '1' => ['_', 'N', 'halt'],
            ],
        ],
        'q0',
        ['halt']
    );
}

function formatUnaryAdditionSummary(string $input, string $result): string
{
    $plusPos = strpos($input, '+');
    if ($plusPos === false) {
        return sprintf('結果: %s', $result);
    }

    $left = substr_count(substr($input, 0, $plusPos), '1');
    $right = substr_count(substr($input, $plusPos + 1), '1');

    return sprintf('結果: %s (%d + %d = %d)', $result, $left, $right, strlen($result));
}

function runUnaryAdditionCli(array $argv): int
{
    $input = $argv[1] ?? '111+11';
    $machine = buildUnaryAdditionMachine();

    try {
        $result = $machine->run($input, true);
        echo formatUnaryAdditionSummary($input, $result) . PHP_EOL;
        return 0;
    } catch (Throwable $e) {
        fwrite(STDERR, 'エラー: ' . $e->getMessage() . PHP_EOL);
        return 1;
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit(runUnaryAdditionCli($argv));
}
