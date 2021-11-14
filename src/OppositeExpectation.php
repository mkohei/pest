<?php

declare(strict_types=1);

namespace Pest;

use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Exporter\Exporter;

/**
 * @internal
 *
 * @mixin Expectation
 */
final class OppositeExpectation
{
    /**
     * Creates a new opposite expectation.
     */
    public function __construct(private Expectation $original)
    {
        // ..
    }

    /**
     * Asserts that the value array not has the provided $keys.
     *
     * @param array<int, int|string> $keys
     */
    public function toHaveKeys(array $keys): Expectation
    {
        foreach ($keys as $key) {
            try {
                $this->original->toHaveKey($key);
            } catch (ExpectationFailedException) {
                continue;
            }

            $this->throwExpectationFailedException('toHaveKey', [$key]);
        }

        return $this->original;
    }

    /**
     * Handle dynamic method calls into the original expectation.
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $name, array $arguments): Expectation
    {
        try {
            /* @phpstan-ignore-next-line */
            $this->original->{$name}(...$arguments);
        } catch (ExpectationFailedException) {
            return $this->original;
        }

        // @phpstan-ignore-next-line
        $this->throwExpectationFailedException($name, $arguments);
    }

    /**
     * Handle dynamic properties gets into the original expectation.
     */
    public function __get(string $name): Expectation
    {
        try {
            /* @phpstan-ignore-next-line */
            $this->original->{$name};
        } catch (ExpectationFailedException) {
            return $this->original;
        }

        // @phpstan-ignore-next-line
        $this->throwExpectationFailedException($name);
    }

    /**
     * Creates a new expectation failed exception with a nice readable message.
     *
     * @param array<int, mixed> $arguments
     */
    private function throwExpectationFailedException(string $name, array $arguments = []): void
    {
        $exporter = new Exporter();

        $toString = fn ($argument): string => $exporter->shortenedExport($argument);

        throw new ExpectationFailedException(sprintf('Expecting %s not %s %s.', $toString($this->original->value), strtolower((string) preg_replace('/(?<!\ )[A-Z]/', ' $0', $name)), implode(' ', array_map(fn ($argument): string => $toString($argument), $arguments))));
    }
}
