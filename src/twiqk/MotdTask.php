<?php


namespace twiqk\OneCore;


use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

use twiqk\OneCore\Main;

class MotdTask extends Task
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
        $control = (int)(rand(0, 2));
        if($control === 0)
        {
            $this->server->getNetwork()->setName($this->config->get("mmessage1"));
        }
        elseif ($control === 1)
        {
            $this->server->getNetwork()->setName($this->config->get("mmessage2"));
        }
        else
        {
            $this->server->getNetwork()->setName($this->config->get("mmessage3"));
        }
    }

}
