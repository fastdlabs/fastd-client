<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2017
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace Process;


use FastD\Packet\Json;
use FastD\Process\AbstractProcess;
use swoole_process;
use swoole_client;

/**
 * Class NodeProcess
 * @package Process
 */
class SubscribeProcess extends AbstractProcess
{
    protected $available = [];

    public function connect(swoole_client $client)
    {
        echo 'subscribe' . PHP_EOL;
    }

    /**
     * @param swoole_client $client
     * @param $data
     * @throws \FastD\Packet\Exceptions\PacketException
     */
    public function notify(swoole_client $client, $data)
    {
        $nodes = Json::decode($data);
        if (empty($nodes)) {
            foreach ($this->available as $node) {
                cache()->deleteItem('node.'.$node);
            }
            cache()->deleteItem('available');
        } else {
            foreach ($nodes as $node) {
                $this->available[] = $node['name'];
                $node = cache()->getItem('node.'.$node['name'])->set(Json::encode($node));
                cache()->save($node);
            }
            $this->available = array_unique($this->available);
            $available = cache()->getItem('available');
            $available->set(Json::encode($this->available));
            cache()->save($available);
        }

    }

    public function close(swoole_client $client)
    {
        echo 'close' . PHP_EOL;
    }

    /**
     * Subscribe registry nodes.
     *
     * @param swoole_process $swoole_process
     * @return callable|void
     */
    public function handle(swoole_process $swoole_process)
    {
        $client = client(config()->get('register.host'), true, false);
        $client->on('connect', [$this, 'connect']);
        $client->on('receive', [$this, 'notify']);
        $client->on('close', [$this, 'close']);
        $client->start();
    }
}