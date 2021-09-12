<?php

namespace Drupal\cluedo\Models;

class Cluedo
{
  private string $key;
  private int $nodeId;
  private bool $gameOver;
  private Solution $solution;
  private array $witnesses;

  /**
   * @param string $gameKey
   * @param int $nodeId
   * @param bool $gameOver
   * @param Solution $solution
   * @param array $witnesses
   */
  public function __construct(string $gameKey, int $nodeId, bool $gameOver, Solution $solution, array $witnesses)
  {
    $this->key = $gameKey;
    $this->nodeId = $nodeId;
    $this->gameOver = $gameOver;
    $this->solution = $solution;
    $this->witnesses = $witnesses;
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

  /**
   * @return array
   */
  public function getWitnesses(): array
  {
    return $this->witnesses;
  }




}
