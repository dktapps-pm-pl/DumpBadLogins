<?php

namespace dktapps\DumpBadLogins;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
	/** @var LoginPacket[] */
	private $packets = [];
	/** @var resource */
	private $file;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->file = fopen($this->getDataFolder() . "xbl_not_authed.log", "ab");

		if(!$this->getServer()->requiresAuthentication()){
			$this->getLogger()->critical("This plugin won't work if you disable \"online-mode\"! Please enable \"online-mode\" in server.properties.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
	}

	/**
	 * @param DataPacketReceiveEvent $ev
	 *
	 * @priority LOWEST (we always want to get this before anything else does)
	 */
	public function onPacketReceive(DataPacketReceiveEvent $ev){
		if(($packet = $ev->getPacket()) instanceof LoginPacket){
			$this->packets[spl_object_hash($ev->getPlayer())] = clone $packet;
		}
	}

	/**
	 * @param PlayerKickEvent $ev
	 *
	 * @priority LOWEST (we always want to get this before anything else does)
	 */
	public function onPlayerKick(PlayerKickEvent $ev){
		if($ev->getReason() === "disconnectionScreen.notAuthenticated"){
			$player = $ev->getPlayer();
			$packet = $this->packets[$hash = spl_object_hash($player)];

			$message = "%s is not logged into Xbox Live!";
			$type = "not XBOX";

			foreach($packet->chainData["chain"] as $token){
				$claims = base64_decode(strtr(explode('.', $token)[1], '-_', '+/'), true);
				if($claims === false){
					continue; ///wtf?
				}

				$claims = json_decode($claims, true);
				if(isset($claims["extraData"]["XUID"]) and $claims["extraData"]["XUID"] !== ""){
					$message = "%s has an XUID, but their keychain is not signed by Mojang (bug or modified client): \"" . $claims["extraData"]["XUID"] . "\"";
					$type = "XBOX but not signed";
					break;
				}
			}

			$this->getLogger()->warning(sprintf($message, $player->getName()));
			fwrite($this->file, $player->getName() . ": " . $type . ": 0x" . bin2hex($packet->buffer) . "\n");


			unset($this->packets[$hash]);
			$ev->setCancelled(); //don't kick me pls
		}
	}

	public function onPlayerLogin(PlayerLoginEvent $ev){
		unset($this->packets[spl_object_hash($ev->getPlayer())]);
	}
}