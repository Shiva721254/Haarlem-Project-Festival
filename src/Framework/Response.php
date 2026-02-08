<?php
declare(strict_types=1);

namespace App\Framework;

final class Response
{
    public function __construct(
        private string $body,
        private int $status = 200,
        /** @var array<string,string> */
        private array $headers = []
    ) {}

    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /**
     * @param array<mixed>|object $data
     */
    public static function json(array|object $data, int $status = 200): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return new self('{"error":"Failed to encode JSON"}', 500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ]);
        }

        return new self($json, $status, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }


public static function redirect(string $to, int $status = 302): self
{
    // For redirects, the body is typically empty
    return (new self('', $status))
        ->withHeader('Location', $to);
}




    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}
