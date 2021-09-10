<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\Clue;
use Drupal\cluedo\Models\Deck;
use Drupal\cluedo\Models\Player;
use Drupal\cluedo\Models\Solution;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class Repository
{

  private EntityStorageInterface $nodeStorage;

  /**
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager)
  {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * Fetches all clues/cards and returns them as Deck object
   * @return Deck
   */
  public function fetchAllClues(): Deck
  {
    $type = ['weapon', 'room', 'suspect'];
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', $type, 'IN');
    $clueArray = $query->execute() ? $this->nodeStorage->loadMultiple($query->execute()) : [];

    $deck = new Deck();

    foreach ($clueArray as $clue) {
      $deck->addCard(new Clue($clue->id(), $clue->bundle(), $clue->label()));
    }

    return $deck;
  }

  /**
   * Fetches an array of player objects from a specific game, based on a gameKey
   * @param $gameKey
   * @return Player[]
   */
  public function fetchPlayersByKey($gameKey): array
  {
    $game = $this->fetchGameEntity($gameKey);

    $players = $game? $game->get('field_witnesses')->referencedEntities() : [];
    $playersArray = [];

    foreach ($players as $player)
    {
      $clues = $player->get('field_clues')->referencedEntities();
      $cluesArray = [];

      foreach ($clues as $clue) {
        $cluesArray[] = new Clue($clue->id(), $clue->bundle(), $clue->label());
      }

      $playersArray[] = new Player($player->id(), $player->label(), $cluesArray);
    }
    return $playersArray;

  }

  public function fetchSolutionByKey(string $gameKey): ?Solution
  {
    $game = $this->fetchGameEntity($gameKey);

    if (!$game)
    {return null;}

    $room = reset($game->get('field_murder_room')->referencedEntities());
    $weapon = reset($game->get('field_murder_weapon')->referencedEntities());
    $murderer = reset($game->get('field_murderer')->referencedEntities());

    return new Solution(
      new Clue($room->id(),$room->bundle(),$room->label()),
      new Clue($weapon->id(),$weapon->bundle(),$weapon->label()),
      new Clue($murderer->id(),$murderer->bundle(),$murderer->label()),
    );

  }

  public function gameIsOver($key): bool
  {
    $query = $this->nodeStorage->getQuery();
    $query->condition('field_game_key', $key, 'LIKE');
    $query->condition('field_game_over', true, '=');

    return (bool)$query->execute();

  }

  public function fetchGameEntity($gameKey): ?EntityInterface
  {
    $gameQuery = $this->nodeStorage->getQuery();
    $gameQuery->condition('field_game_key', $gameKey, 'LIKE');
    $gameQuery = $gameQuery->execute();

    return $this->nodeStorage->load(reset($gameQuery));
  }
}

