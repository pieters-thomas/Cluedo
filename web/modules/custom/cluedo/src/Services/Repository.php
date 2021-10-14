<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\Cluedo;
use Drupal\cluedo\Models\Clues\CluedoClue;
use Drupal\cluedo\Models\Clues\Room;
use Drupal\cluedo\Models\Clues\Suspect;
use Drupal\cluedo\Models\Clues\Weapon;
use Drupal\cluedo\Models\Deck;
use Drupal\cluedo\Models\Witness;
use Drupal\cluedo\Models\Solution;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Exception;

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
   * @throws Exception
   */
  public function fetchAllClues(): Deck
  {
    $type = ['weapon', 'room', 'suspect'];
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', $type, 'IN');

    $clueEntities = $this->nodeStorage->loadMultiple($query->execute()) ?? [];
    $clueArray = [];

    foreach ($clueEntities as $entity) {
      $clueArray[] = $this->entityToClue($entity);
    }

    return new Deck($clueArray);
  }

  /**
   * @throws Exception
   */
  public function fetchGame($gameKey): ?Cluedo
  {
    $gameQuery = $this->nodeStorage->getQuery();
    $gameQuery->condition('field_game_key', $gameKey, 'LIKE');
    $gameQuery = $gameQuery->execute();

    $game = $this->nodeStorage->load(reset($gameQuery));

    if (!$game) {
      return null;
    }

    return new Cluedo(
      $gameKey,
      $game->id(),
      $game->get('field_game_over')->getValue()[0]["value"],
      $this->extractSolution($game),
    );
  }


  /**
   * @throws Exception
   */
  public function entityToClue(EntityInterface $clueEntity): CluedoClue
  {
    switch ($clueEntity->bundle()) {
      case 'weapon':
        return new Weapon($clueEntity->id(), $clueEntity->label());
      case 'room':
        return new Room($clueEntity->id(), $clueEntity->label());
      case 'suspect':
        return new Suspect($clueEntity->id(), $clueEntity->label(), $clueEntity->get('field_color')->getValue()[0]["value"]);
    }
    throw new Exception("Could not convert entity to clue object");
  }

  /**
   * @throws Exception
   */
  public function extractWitnesses(EntityInterface $game): array
  {
    $witnessEntities = $game->get('field_witnesses')->referencedEntities();
    $witnesses = [];

    foreach ($witnessEntities as $entity) {

      $profile = $this->entityToClue($entity->get('field_profile')->referencedEntities()[0]);
      $clueEntities = $entity->get('field_clues')->referencedEntities();
      $cluesArray = [];

      foreach ($clueEntities as $clueEntity) {
        $cluesArray[] = $this->entityToClue($clueEntity);
      }

      $witnesses[] = new Witness($entity->id(), $profile, $cluesArray);
    }

    return $witnesses;
  }

  public function extractSolution(EntityInterface $game): Solution
  {
    $room = reset($game->get('field_murder_room')->referencedEntities());
    $weapon = reset($game->get('field_murder_weapon')->referencedEntities());
    $murderer = reset($game->get('field_murderer')->referencedEntities());

    return new Solution(
      new Room($room->id(), $room->label()),
      new Weapon($weapon->id(), $weapon->label()),
      new Suspect($murderer->id(), $murderer->label(), $murderer->get('field_color')->getValue()[0]["value"]),
    );
  }

}

