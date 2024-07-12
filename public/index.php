<?php

class Main
{
    public function __invoke(): void
    {
        $lines = $this->readDataFromFile();
        $results = [];

        foreach ($lines as $line) {
            preg_match('/(\d+)([+*\/-])(\d+)/', $line, $matches);
            [,$num1, $operator, $num2] = $matches;

            $num1 = str_split($num1);
            $num2 = str_split($num2);

            $result = match ($operator) {
                '+' => $this->add($num1, $num2),
                '-' => $this->subtract($num1, $num2),
                '*' => $this->multiply($num1, $num2),
                default => "invalid operator: {$operator}\n",
            };

            $results = [...$results, $result];
        }

        $this->saveDataIntoFile(implode("\n", $results));
    }

    private function add(array $num1, array $num2, bool $isPrint = true): string|array
    {
        $resultNum = [];
        $carry = 0;
        $maxLen = max(count($num1), count($num2));

        $num1Rev = $this->toIntWithReverse($num1);
        $num2Rev = $this->toIntWithReverse($num2);

        for ($i = 0; $i < $maxLen; $i++) {
            $digit1 = $num1Rev[$i] ?? 0;
            $digit2 = $num2Rev[$i] ?? 0;
            $sum = $digit1 + $digit2 + $carry;

            if ($sum >= 10) {
                $carry = 1;
                $sum -= 10;
            } else {
                $carry = 0;
            }

            $resultNum = [$sum, ...$resultNum];
        }

        if ($carry) {
            $resultNum = [$carry, ...$resultNum];
        }

        $maxLen++;

        if (false === $isPrint) {
            return $resultNum;
        }

        return implode('', [
            sprintf("%s\n", $this->addPaddingIntoString($this->arrayIntoString($num1), $maxLen)),
            sprintf("%s%s\n", '+', $this->addPaddingIntoString($this->arrayIntoString($num2), $maxLen - 1)),
            sprintf("%s\n", $this->generateLine($maxLen)),
            sprintf("%s\n", $this->addPaddingIntoString($this->arrayIntoString($resultNum), $maxLen)),
        ]);
    }

    private function subtract(array $num1, array $num2, bool $isPrint = true): string|array
    {
        $resultNum = [];
        $borrow = 0;
        $maxLen = max(count($num1), count($num2));

        $num1Rev = $this->toIntWithReverse($num1);
        $num2Rev = $this->toIntWithReverse($num2);

        for ($i = 0; $i < $maxLen; $i++) {
            $digit1 = $num1Rev[$i] ?? 0;
            $digit2 = $num2Rev[$i] ?? 0;
            $diff = $digit1 - $digit2 - $borrow;

            if ($diff < 0) {
                $borrow = 1;
                $diff += 10;
            } else {
                $borrow = 0;
            }

            $resultNum = [$diff, ...$resultNum];
        }

        $maxLen++;

        if (false === $isPrint) {
            return $resultNum;
        }

        return implode('', [
            sprintf("%s\n", $this->addPaddingIntoString($this->arrayIntoString($num1), $maxLen)),
            sprintf("%s%s\n", '-', $this->addPaddingIntoString($this->arrayIntoString($num2), $maxLen - 1)),
            sprintf("%s\n", $this->generateLine($maxLen)),
            sprintf("%s\n", $this->addPaddingIntoString($this->arrayIntoString($resultNum), $maxLen)),
        ]);
    }

    private function multiply(array $num1, array $num2, bool $isPrint = true): string
    {
        $maxLen = max(count($num1), count($num2));

        $num1Rev = $this->toIntWithReverse($num1);
        $num2Rev = $this->toIntWithReverse($num2);

        $products = [];

        for ($i = 0; $i < count($num2); $i++) {
            $product = [];
            $carry = 0;

            for ($j = 0; $j < count($num1); $j++) {
                $digit1 = $num1Rev[$j];
                $digit2 = $num2Rev[$i];
                $prod = $digit1 * $digit2 + $carry;

                if ($prod >= 10) {
                    $carry = intdiv($prod, 10);
                    $prod %= 10;
                } else {
                    $carry = 0;
                }

                $product = [$prod, ...$product];
            }

            if (0 !== $carry) {
                $product = [$carry, ...$product];
            }

            $totalProd = count($product);
            for ($k = 0; $k < $totalProd; $k++) {
                if ($product[$k] === 0 && $k !== $totalProd - 1) {
                    $product[$k] = null;
                    continue;
                }

                break;
            }

            for ($k = 0; $k < $i; $k++) {
                $product = [...$product, null];
            }

            $products = [...$products, $product];
        }

        $resultNum = $products[0];
        for ($i = 1; $i < count($products); $i++) {
            $resultNum = $this->add($resultNum, $products[$i], false);
        }

        if (false === $isPrint) {
            return $resultNum;
        }

        $maxLen++;

        $maxLenProd = max(array_map(fn($product) => count($product), $products)) + 1;

        if ($maxLen < $maxLenProd) {
            $maxLen = $maxLenProd;
        }

        $result = [
            sprintf("%s\n", $this->addPaddingIntoString($this->arrayIntoString($num1), $maxLen)),
            sprintf("%s%s\n", '*', $this->addPaddingIntoString($this->arrayIntoString($num2), $maxLen - 1)),
            sprintf("%s\n", $this->generateLine($maxLen)),
        ];

        if (1 !== count($products)) {
            $totalProds = count($products);
            for ($i = 0; $i < $totalProds; $i++) {
                if ($i !== $totalProds - 1) {
                    $result = [...$result, sprintf("%s\n", $this->addPaddingIntoString($this->arrayIntoString(array_map(fn($dig) => $dig ?? ' ', $products[$i])), $maxLen))];
                    continue;
                }

                $result = [...$result, sprintf("%s\n", $this->addPaddingIntoString(sprintf('+%s', $this->arrayIntoString(array_map(fn($dig) => $dig ?? ' ', $products[$i]))), $maxLen - 1))];
            }

            $result = [...$result, sprintf("%s\n", $this->generateLine($maxLen))];
        }

        $result = [...$result, sprintf("%s\n", $this->addPaddingIntoString($this->arrayIntoString($resultNum), $maxLen))];

        return implode('', $result);
    }

    private function arrayIntoString(array $arr): string
    {
        return implode('', $arr);
    }

    private function addPaddingIntoString(string $str, int $maxLen): string
    {
        return str_pad($str, $maxLen, ' ', STR_PAD_LEFT);
    }

    private function generateLine(int $maxLen): string
    {
        return str_repeat('-', $maxLen);
    }

    private function toIntWithReverse(array $arr): array
    {
        return array_map(fn($dig) => +$dig, array_reverse($arr));
    }

    private function readDataFromFile(): array
    {
        $text = file_get_contents(__DIR__ . '/input.txt');

        $lines = explode("\n", $text);
        array_shift($lines);

        return $lines;
    }

    private function saveDataIntoFile(string $data): void
    {
        file_put_contents(__DIR__ . '/output.txt', $data, LOCK_EX);
    }
}

(new Main())();