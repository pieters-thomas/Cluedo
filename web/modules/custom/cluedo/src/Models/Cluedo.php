<?php

namespace Drupal\cluedo\Models;

class Cluedo
{
  private string $key;
  private int $nodeId;
  private bool $gameOver;
  private Solution $solution;

  /**
   * @param string $gameKey
   * @param int $nodeId
   * @param bool $gameOver
   * @param Solution $solution
   */
  public function __construct(string $gameKey, int $nodeId, bool $gameOver, Solution $solution)
  {
    $this->key = $gameKey;
    $this->nodeId = $nodeId;
    $this->gameOver = $gameOver;
    $this->solution = $solution;
  }

  /**
   * @return string
   */
  public function getKey(): string
  {
    return $this->key;
  }

  /**
   * @return int
   */
  public function getNodeId(): int
  {
    return $this->nodeId;
  }

  /**
   * @return bool
   */
  public function isGameOver(): bool
  {
    return $this->gameOver;
  }

  /**
   * @return Solution
   */
  public function getSolution(): Solution
  {
    return $this->solution;
  }
}
