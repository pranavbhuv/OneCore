<?php


namespace twiqk\OneCore;


use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use twiqk\OneCore\Main;

class BroadcastTask extends Task
{


    /** @var Server */
    private $server;
    /** @var Config */
    private $config;

    public function __construct(Server $server, Config $config)
    {
        $this->server = $server;
        $this->config = $config;
    }

    /** TO whoever is reading this, I know this code is disgusting. But it works.
     * @param int $currentTick
     */

    public function onRun(int $currentTick) : void
    {
        $control = (int)(rand(0, 3));
        if($control === 0)
        {
            $this->server->broadcastMessage($this->config->get("message1"));
        }
        elseif ($control === 1)
        {
            $this->server->broadcastMessage($this->config->get("message2"));
        }
        elseif ($control === 2)
        {
            $this->server->broadcastMessage($this->config->get("message3"));
        }
        else
        {
            $this->server->broadcastMessage($this->config->get("message4"));
        }
    }

}
