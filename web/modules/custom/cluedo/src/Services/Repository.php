<?php

namespace Drupal\cluedo\Services;

use Drupal;
use Drupal\cluedo\Models\Clue;
use Drupal\cluedo\Models\Deck;
use Drupal\cluedo\Models\Player;
use Drupal\cluedo\Models\Solution;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use PDO;

class Repository
{

  private Drupal\Core\Entity\EntityStorageInterface $nodeStorage;
  private Drupal\Core\Database\Connection $database;

  /**
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function __construct(Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager, Drupal\Core\Database\Connection $database)
  {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->database = $database;
  }


  /**
   * Fetches all clues/cards and returns them as an array of clue objects
   * @return Deck
   */
  public function fetchAllClues(): Deck
  {
    $type = ['weapon','room','suspect'];
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', $type, 'IN');
    $clueArray = $query->execute()? $this->nodeStorage->loadMultiple($query->execute()): [];

    $deck = new Deck();

    foreach ($clueArray as $clue)
    {
      $deck->addCard(new Clue($clue->id(), $clue->bundle(), $clue->label()));
    }

    return $deck;
  }

  /**
   * Returns a single clue object based on title input
   * @param string $title
   * @return Clue
   */
  public function fetchClueByTitle(string $title): Clue
  {
    $query = $this->database->select('node', 'n');
    $query->leftJoin('node_field_revision', 'nfr', 'n.nid = nfr.nid');
    $query->addField('n', 'nid', 'id');
    $query->addField('n', 'type', 'type');
    $query->addField('nfr', 'title', 'name');
    $query->condition('title', $title, 'LIKE');
    $clue = $query->execute()->fetch(PDO::FETCH_ASSOC);

    return new Clue($clue['id'], $clue['type'], $clue['name']);
  }


  /**
   * @param $key
   * @return Player[]
   */
  public function fetchPlayersByKey($key): array
  {


    $query = $this->database->select('node__field_game_key', 'key');
    $query->addField('nfgp', 'field_witnesses_target_id', 'playerId');
    $query->addField('nfr', 'title', 'playerName');
    $query->addField('nfpc', 'field_clues_target_id', 'clueId');
    $query->addField('nfr2', 'title', 'clueName');
    $query->addField('n', 'type', 'clueType');
    $query->leftJoin('node__field_witnesses', 'nfgp', 'nfgp.entity_id = key.entity_id');
    $query->leftJoin('node__field_profile', 'nfpp', 'nfpp.entity_id = nfgp.field_witnesses_target_id');
    $query->leftJoin('node_field_revision', 'nfr', 'nfpp.field_profile_target_id = nfr.nid');
    $query->leftJoin('node__field_clues', 'nfpc', 'nfpc.entity_id  = nfgp.field_witnesses_target_id');
    $query->leftJoin('node_field_revision', 'nfr2', 'nfpc.field_clues_target_id = nfr2.nid');
    $query->leftJoin('node', 'n', 'n.nid = nfpc.field_clues_target_id');
    $query->condition('field_game_key_value', $key, 'LIKE');
    $array = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

      $test = [];
      foreach ($array as $row) {
        $test[$row['playerName']]['name'] = $row['playerName'];
        $test[$row['playerName']]['id'] = $row['playerId'];
        $test[$row['playerName']]['clues'][] = new Clue($row['clueId'], $row['clueType'], $row['clueName']);
      }


      $players = [];
      foreach ($test as $player) {
        $players[] = new Player($player['id'], $player['name'], $player['clues']);
      }

      return $players;
  }

  public function fetchSolutionByKey(string $gameKey): Solution|string
  {

    $query = $this->database->select('node__field_game_key', 'key');
    $query->innerJoin('node__field_murder_room', 'r', 'key.entity_id = r.entity_id');
    $query->innerJoin('node__field_murder_weapon', 'w', 'key.entity_id = w.entity_id');
    $query->innerJoin('node__field_murderer', 'm', 'key.entity_id = m.entity_id');
    $query->innerJoin('node_field_revision', 'n1', 'r.field_murder_room_target_id = n1.nid ');
    $query->innerJoin('node_field_revision', 'n2', 'w.field_murder_weapon_target_id = n2.nid ');
    $query->innerJoin('node_field_revision', 'n3', 'm.field_murderer_target_id = n3.nid ');
    $query->addField('r', 'field_murder_room_target_id', 'roomId');
    $query->addField('n1', 'title', 'roomName');
    $query->addField('w', 'field_murder_weapon_target_id', 'weaponId');
    $query->addField('n2', 'title', 'weaponName');
    $query->addField('m', 'field_murderer_target_id', 'murdererId');
    $query->addField('n3', 'title', 'murdererName');
    $query->condition('field_game_key_value', $gameKey, 'LIKE');
    $solution = $query->execute()->fetch(PDO::FETCH_ASSOC);

    return new Solution(
      new Clue((int)$solution['roomId'], 'room', $solution['roomName']),
      new Clue((int)$solution['weaponId'], 'weapon', $solution['weaponName']),
      new Clue((int)$solution['murdererId'], 'suspect', $solution['murdererName']),
    );

  }
}

