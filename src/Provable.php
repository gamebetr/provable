<?php

namespace Gamebetr\Provable;

class Provable implements ProvableInterface
{
    /**
     * client seed.
     * @var string
     */
    private $clientSeed;

    /**
     * server seed.
     * @var string
     */
    private $serverSeed;

    /**
     * min number.
     * @var int
     */
    private $min;

    /**
     * max number.
     * @var int
     */
    private $max;

    /**
     * type.
     * @var string
     */
    private $type;

    /**
     * If the random seed has already been set for mt_rand().
     *
     * @var bool
     */
    protected $random_seed_set = false;

    /**
     * class constructor.
     * @param string $clientSeed
     * @param string $serverSeed
     * @param int $min
     * @param int $max
     * @param string $type
     */
    public function __construct(string $clientSeed = null, string $serverSeed = null, int $min = 0, int $max = 0, string $type = 'number')
    {
        $this->setClientSeed($clientSeed);
        $this->setServerSeed($serverSeed);
        $this->setMin($min);
        $this->setMax($max);
        $this->setType($type);
    }

    /**
     * static constructor.
     * @param string $clientSeed
     * @param string $serverSeed
     * @param int $min
     * @param int $max
     * @param string $type
     * @return \Gamebetr\Provable\ProvableInterface
     */
    public static function init(string $clientSeed = null, string $serverSeed = null, int $min = 0, int $max = 0, string $type = 'number'): ProvableInterface
    {
        return new static($clientSeed, $serverSeed, $min, $max, $type);
    }

    /**
     * client seed setter.
     * @param string $clientSeed
     * @return \Gamebetr\Provable\ProvableInterface
     */
    public function setClientSeed(string $clientSeed = null): ProvableInterface
    {
        $this->clientSeed = $clientSeed ?? $this->generateRandomSeed();

        return $this;
    }

    /**
     * client seed getter.
     * @return string
     */
    public function getClientSeed():  string
    {
        return $this->clientSeed;
    }

    /**
     * server seed setter.
     * @param string $serverSeed
     * @return \Gamebetr\Provable\ProvableInterface
     */
    public function setServerSeed(string $serverSeed = null): ProvableInterface
    {
        $this->serverSeed = $serverSeed ?? $this->generateRandomSeed();

        return $this;
    }

    /**
     * server seed getter.
     * @return string
     */
    public function getServerSeed(): string
    {
        return $this->serverSeed;
    }

    /**
     * hashed server seed getter.
     * @return string
     */
    public function getHashedServerSeed(): string
    {
        return hash('sha256', $this->getServerSeed());
    }

    /**
     * min setter.
     * @param int $min
     * @return \Gamebetr\Provable\ProvableInterface
     */
    public function setMin(int $min): ProvableInterface
    {
        $this->min = $min;

        return $this;
    }

    /**
     * min getter.
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * max setter.
     * @param int $max
     * @return \Gamebetr\Provable\ProvableInterface
     */
    public function setMax(int $max): ProvableInterface
    {
        $this->max = $max;

        return $this;
    }

    /**
     * max getter.
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * type setter.
     * @param string $type - number|shuffle
     * @return \Gamebetr\Provable\ProvableInterface
     */
    public function setType(string $type): ProvableInterface
    {
        if (! in_array($type, ['number', 'shuffle'])) {
            throw new \Exception("Invalid type $type", 400);
        }
        $this->type = $type;

        return $this;
    }

    /**
     * type getter.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * returns the results.
     * @return int|array
     */
    public function results()
    {
        if ($this->getType() == 'number') {
            return $this->number();
        }
        if ($this->getType() == 'shuffle') {
            return $this->shuffle();
        }
    }

    /**
     * returns a random number within a range.
     * @param int $minimumNumber
     * @param int $maximumNumber
     * @return int
     */
    public function number(int $minimumNumber = null, int $maximumNumber = null) :int
    {
        if ($minimumNumber !== null) {
            $this->setMin($minimumNumber);
        }
        if ($maximumNumber !== null) {
            $this->setMax($maximumNumber);
        }
        if (! $this->random_seed_set) {
            $this->random_seed_set = true;
            mt_srand($this->generateSeedInteger());
        }

        return mt_rand($this->getMin(), $this->getMax());
    }

    /**
     * returns a random shuffle of numbers within a range
     * uses fisher yates shuffle (https://en.wikipedia.org/wiki/Fisher–Yates_shuffle).
     * @param int $minimumNumber
     * @param int $maximumNumber
     * @return array
     */
    public function shuffle(int $minimumNumber = null, int $maximumNumber = null):array
    {
        if ($minimumNumber !== null) {
            $this->setMin($minimumNumber);
        }
        if ($maximumNumber !== null) {
            $this->setMax($maximumNumber);
        }
        $range = range($this->getMin(), $this->getMax());
        mt_srand($this->generateSeedInteger());
        for ($i = count($range) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $range[$i];
            $range[$i] = $range[$j];
            $range[$j] = $tmp;
        }

        return $range;
    }

    /**
     * generate a seed integer from server seed and client seed.
     * @return int
     */
    private function generateSeedInteger(): int
    {
        return hexdec(substr(hash_hmac('sha256', $this->getServerSeed(), $this->getClientSeed()), -8, 8));
    }

    /**
     * generate a random seed.
     * @var int
     * @return string
     */
    private function generateRandomSeed(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    /**
     * Reset the class to start the results over.
     * @return self
     */
    public function reset(): ProvableInterface
    {
        $this->random_seed_set = false;

        return $this;
    }
}
