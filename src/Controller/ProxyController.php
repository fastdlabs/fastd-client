<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2017
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace Controller;


use FastD\Http\ServerRequest;
use FastD\Http\Uri;
use FastD\Packet\Json;

class ProxyController
{
    /**
     * @param ServerRequest $request
     * @return \FastD\Http\Response
     * @throws \Exception
     * @throws \FastD\Packet\Exceptions\PacketException
     */
    public function forward(ServerRequest $request)
    {
        $service = $request->getHeaderLine('service');
        if (empty($service)) {
            $available = cache()->getItem('available');
            if ($available->isHit()) {
                return json(Json::decode($available->get()));
            }
            return json();
        }
        $node = cache()->getItem('node.'.$service);
        if (!$node->isHit()) {
            abort(404, sprintf('service %s is not found', $service));
        }
        $node = Json::decode($node->get());
        $uri = sprintf(
            '%s://%s:%s%s',
            'http',
            $node['host'],
            $node['port'],
            $request->getUri()->getPath()
        );
        $client = clone $request;
        return $client->withUri(new Uri($uri))->send();
    }
}