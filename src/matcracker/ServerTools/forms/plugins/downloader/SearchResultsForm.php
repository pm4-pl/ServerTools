<?php

/*
 *    _________                              ___________           .__
 *	 /   _____/ ______________  __ __________\__    ___/___   ____ |  |   ______
 *	 \_____  \_/ __ \_  __ \  \/ // __ \_  __ \|    | /  _ \ /  _ \|  |  /  ___/
 *	 /        \  ___/|  | \/\   /\  ___/|  | \/|    |(  <_> |  <_> )  |__\___ \
 *	/_______  /\___  >__|    \_/  \___  >__|   |____| \____/ \____/|____/____  >
 *			\/     \/                 \/                                     \/
 *
 * Copyright (C) 2020
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author matcracker
 * @link https://www.github.com/matcracker/ServerTools
 *
*/

declare(strict_types=1);

namespace matcracker\ServerTools\forms\plugins\downloader;

use matcracker\FormLib\Form;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\task\async\GetPluginInfoTask;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use function array_key_exists;
use function count;

final class SearchResultsForm extends Form{

	private static $resultsCache = [];

	/**
	 * SearchResultsForm constructor.
	 *
	 * @param string[][] $results
	 */
	public function __construct(array $results){
		parent::__construct(
			function(Player $player, $data) use ($results){
				if(!array_key_exists((int) $data, $results)){
					throw new PluginException();
				}

				self::$resultsCache[$player->getName()] = $results;

				Server::getInstance()->getAsyncPool()->submitTask(
					new GetPluginInfoTask($results[$data]["name"], $player->getName())
				);
			},
			FormManager::onClose(new SearchPluginForm())
		);

		$this->setTitle(count($results) . " Poggit Result(s)")
			->setMessage("Select a plugin:");

		foreach($results as $result){
			$this->addWebImageButton($result["name"], $result["url"]);
		}
	}

	/**
	 * @param string $playerName
	 *
	 * @return string[][]
	 */
	public static function getResultsCache(string $playerName) : array{
		return self::$resultsCache[$playerName] ?? [];
	}
}