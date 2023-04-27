<?php

namespace App\Data;

use App\Entity\Campus;
use Symfony\Component\Validator\Constraints\Date;

class SearchData
{
    /**
     * @var string
     */
public $q = "";

    /**
     * @var Campus
     */

public $campus;

    /**
     * @var Date
     */
public $dateMin;

    /**
     * @var Date
     */
public $dateMax;


public $organisateur;

public $inscrit;

public $nonInscrit;

public $sortiesTerminees;



}