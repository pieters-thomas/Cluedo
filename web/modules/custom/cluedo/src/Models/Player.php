<?php

namespace Drupal\cluedo\Models;

use JetBrains\PhpStorm\Pure;

class Player
{
  private int $nodeId;
  private string $name;
  /**
   * @var Clue[]
   */
  private array $clues;


  /**
   * @param string $name
   * @param int $nodeId
   * @param Clue[] $clues
   */
  public function __construct( int $nodeId, string $name, array $clues = [])
  {
    $this->nodeId = $nodeId;
    $this->name = $name;
    $this->clues = $clues;
  }

  public function getNodeId(): int
  {
    return $this->nodeId;
  }

  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @return Clue[]
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

  /**
   * @param int $nodeId
   */
  public function setNodeId(int $nodeId): void
  {
    $this->nodeId = $nodeId;
  }

  public function addClue(Clue $clue): void
  {
    $this->clues[] = $clue;
  }

}
