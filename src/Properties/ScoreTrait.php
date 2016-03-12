<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the Search Score methods.
 *
 * @internal
 */
trait ScoreTrait
{
    /**
     * @var float
     */
    private $_score = null;

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->_score;
    }

    /**
     * @param float $value
     *
     * @return self
     */
    private function setScore($value)
    {
        $this->_score = (float) $value;

        return $this;
    }
}
