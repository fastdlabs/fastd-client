<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2017
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace Testing;

use FastD\TestCase;

class ProxyControllerTest extends TestCase
{
    public function testProxyForward()
    {
        $response = $this->handleRequest($this->request('GET', '/', []), [], [
            'Service' => 'fastd-server',
        ]);
        echo $response;
    }
}
