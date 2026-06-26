<?php

namespace Repat\CliCrud\Actions;

class ActionResponse
{
    public function __construct(
        public readonly ?string $message = null,
        public readonly bool $danger = false,
    ) {
    }

    public static function message(string $message): self
    {
        return new self($message, false);
    }

    public static function danger(string $message): self
    {
        return new self($message, true);
    }

    public function isDanger(): bool
    {
        return $this->danger;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
