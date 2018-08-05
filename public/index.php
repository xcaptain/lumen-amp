<?php

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Server;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Socket;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';
/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

Amp\Loop::run(function () use ($app) {
    $sockets = [
        Socket\listen("0.0.0.0:8000"),
        Socket\listen("[::]:8000"),
    ];

    $logger = app('log');

    $server = new Server($sockets, new CallableRequestHandler(function (Request $request) use ($app) {
        $content = yield $request->getBody()->buffer();
        $method = $request->getMethod();
        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                parse_str($content, $params);
                break;
            default:
                parse_str($request->getUri()->getQuery(), $params);
                break;
        }
        $server = [];
        $headers = $request->getHeaders();
        foreach ($headers as $name => $header) {
            $server['HTTP_' . str_replace('-', '_', strtoupper($name))] = $header[0];
        }
        $server['CONTENT_TYPE'] = $headers['content-type'];
        $server['CONTENT_LENGTH'] = $headers['content-length'];

        // process $_FILES
        $form = yield Amp\Http\Server\FormParser\parseForm($request);
        $files = [];
        foreach ($form->getNames() as $name) {
            foreach ($form->getFieldArray($name) as $field) {
                $attribute = $field->getAttributes();
                if (!$attribute->isFile()) {
                    continue;
                }
                $file_location = tempnam(ini_get("upload_tmp_dir"), "uploaded_file");
                file_put_contents($file_location, $field->getValue());

                $target["name"] = $attribute->getFilename();
                $target["type"] = $attribute->getMimeType();
                $target["size"] = strlen($field->getValue());
                $target["tmp_name"] = $file_location;
                $file = new UploadedFile($target['tmp_name'], $target['name'], $target['type'], 0);
                $files[$name] = $file;
            }
        }
        $sfmRequest = SymfonyRequest::create((string)$request->getUri(), $request->getMethod(), $params, $request->getCookies(), $files, $server, $content);
        $response = $app->runAmp($sfmRequest);
        return new Response($response->getStatusCode(), $response->headers->all(), $response->getContent());
    }), $logger);

    yield $server->start();

    // Stop the server gracefully when SIGINT is received.
    // This is technically optional, but it is best to call Server::stop().
    Amp\Loop::onSignal(SIGINT, function (string $watcherId) use ($server) {
        Amp\Loop::cancel($watcherId);
        yield $server->stop();
    });
});
