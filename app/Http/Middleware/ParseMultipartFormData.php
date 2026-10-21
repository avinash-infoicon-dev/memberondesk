<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * PHP does not populate $_POST / $_FILES for multipart PUT/PATCH.
 * Parse the raw body so API clients (Postman, Retrofit) can update with files.
 */
class ParseMultipartFormData
{
    public function handle(Request $request, Closure $next)
    {
        if (! in_array($request->getRealMethod(), ['PUT', 'PATCH'], true)) {
            return $next($request);
        }

        $contentType = $request->header('Content-Type', '');
        if (! str_contains($contentType, 'multipart/form-data')) {
            return $next($request);
        }

        if ($request->request->count() > 0 || $request->files->count() > 0) {
            return $next($request);
        }

        if (! preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $contentType, $matches)) {
            return $next($request);
        }

        $boundary = trim($matches[1] !== '' ? $matches[1] : $matches[2]);
        $raw = $request->getContent();
        if ($raw === '') {
            return $next($request);
        }

        $inputs = [];
        $files = [];

        foreach (array_slice(explode('--'.$boundary, $raw), 1) as $part) {
            if (trim($part) === '--' || trim($part) === '') {
                continue;
            }

            $part = ltrim($part, "\r\n");
            if (! str_contains($part, "\r\n\r\n")) {
                continue;
            }

            [$rawHeaders, $body] = explode("\r\n\r\n", $part, 2);
            $body = preg_replace('/\r\n$/', '', $body) ?? $body;

            if (! preg_match('/content-disposition:\s*form-data;\s*name="([^"]+)"(?:;\s*filename="([^"]*)")?/i', $rawHeaders, $disp)) {
                continue;
            }

            $name = $disp[1];
            $filename = $disp[2] ?? null;

            if ($filename !== null && $filename !== '') {
                $tmp = tempnam(sys_get_temp_dir(), 'putmultipart');
                if ($tmp === false) {
                    continue;
                }
                file_put_contents($tmp, $body);

                $files[$name] = new UploadedFile(
                    $tmp,
                    $filename,
                    $this->headerValue($rawHeaders, 'Content-Type') ?? 'application/octet-stream',
                    null,
                    true
                );
            } else {
                $inputs[$name] = $body;
            }
        }

        $request->request->add($inputs);

        if ($files !== []) {
            $fileBag = new FileBag($files);
            $request->files->add($fileBag->all());
        }

        return $next($request);
    }

    private function headerValue(string $rawHeaders, string $header): ?string
    {
        if (! preg_match('/'.$header.':\s*(.+)/i', $rawHeaders, $match)) {
            return null;
        }

        return trim($match[1]);
    }
}
