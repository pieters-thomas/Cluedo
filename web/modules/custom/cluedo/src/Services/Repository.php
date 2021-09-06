<?php

namespace Drupal\cluedo\Services;

use Drupal;
use Drupal\cluedo\Models\Clue;
use Drupal\cluedo\Models\Deck;
use Drupal\cluedo\Models\Player;
use Drupal\cluedo\Models\Solution;
use PDO;

class Repository
{
  /**
   * Fetches all clues/cards and returns them as an array of clue objects
   * @return Deck
   */
  public function fetchAllClues(): Deck
  {
    $query = Drupal::database()->select('node', 'n');
    $query->leftJoin('node_field_revision', 'nfr', 'n.nid = nfr.nid');
    $query->addField('n', 'nid', 'id');
    $query->addField('n', 'type', 'type');
    $query->addField('nfr', 'title', 'name');
    $query->condition($query->orConditionGroup()
      ->condition('type', 'room', 'LIKE')
      ->condition('type', 'suspect', 'LIKE')
      ->condition('type', 'weapon', 'LIKE'));
    $clueArray = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    $deck = new Deck();

    foreach ($clueArray as $clue) {
      $deck->addCard(new Clue($clue['id'], $clue['type'], $clue['name']));
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
    $query = Drupal::database()->select('node', 'n');
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
    $query = Drupal::database()->select('node__field_game_key', 'key');
    $query->addField('nfgp', 'field_game_players_target_id', 'playerId');
    $query->addField('nfr', 'title', 'playerName');
    $query->addField('nfpc', 'field_player_clues_target_id', 'clueId');
    $query->addField('nfr2', 'title', 'clueName');
    $query->addField('n', 'type', 'clueType');
    $query->leftJoin('node__field_game_players', 'nfgp', 'nfgp.entity_id = key.entity_id');
    $query->leftJoin('node__field_player_profile', 'nfpp', 'nfpp.entity_id = nfgp.field_game_players_target_id');
    $query->leftJoin('node_field_revision', 'nfr', 'nfpp.field_player_profile_target_id = nfr.nid');
    $query->leftJoin('node__field_player_clues', 'nfpc', 'nfpc.entity_id  = nfgp.field_game_players_target_id');
    $query->leftJoin('node_field_revision', 'nfr2', 'nfpc.field_player_clues_target_id = nfr2.nid');
    $query->leftJoin('node', 'n', 'n.nid = nfpc.field_player_clues_target_id');
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

  public function fetchSolutionByKey(string $gameKey): Solution
  {
    $query = Drupal::database()->select('node__field_game_key', 'key');
    $query->innerJoin('node__field_game_room', 'r', 'key.entity_id = r.entity_id');
    $query->innerJoin('node__field_game_weapon', 'w', 'key.entity_id = w.entity_id');
    $query->innerJoin('node__field_game_murderer', 'm', 'key.entity_id = m.entity_id');
    $query->innerJoin('node_field_revision', 'n1', 'r.field_game_room_target_id = n1.nid ');
    $query->innerJoin('node_field_revision', 'n2', 'w.field_game_weapon_target_id = n2.nid ');
    $query->innerJoin('node_field_revision', 'n3', 'm.field_game_murderer_target_id = n3.nid ');
    $query->addField('r', 'field_game_room_target_id', 'roomId');
    $query->addField('n1', 'title', 'roomName');
    $query->addField('w', 'field_game_weapon_target_id', 'weaponId');
    $query->addField('n2', 'title', 'weaponName');
    $query->addField('m', 'field_game_murderer_target_id', 'murdererId');
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

