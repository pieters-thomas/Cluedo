<?php

namespace Drupal\cluedo\Models;

use Drupal\cluedo\Models\Clues\CluedoClue;
use JetBrains\PhpStorm\Pure;

class Witness
{
  private int $nodeId;
  private CluedoClue $profile;
  /**
   * @var CluedoClue[]
   */
  private array $clues;


  /**
   * @param string $name
   * @param int $nodeId
   * @param CluedoClue[] $clues
   */
  public function __construct( int $nodeId, CluedoClue $profile, array $clues = [])
  {
    $this->nodeId = $nodeId;
    $this->profile = $profile;
    $this->clues = $clues;
  }

  public function getNodeId(): int
  {
    return $this->nodeId;
  }

  public function getProfile(): CluedoClue
  {
    return $this->profile;
  }

  /**
   * @return CluedoClue[]
   */
  public function getClues(): array
  {
    return $this->clues;
  }

  #[Pure] public function getClueIds():array
  {
    $clueIds = [];
    foreach ($this->clues as $clue)
    {
      $clueIds[] = $clue->getNodeId();
    }
    return $clueIds;
  }

  public function addClue(CluedoClue $clue): void
  {
    $this->clues[] = $clue;
  }

}
