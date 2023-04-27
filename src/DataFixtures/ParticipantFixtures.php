<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $userPasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher= $userPasswordHasher;
    }


    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $campus= $manager->getRepository(Campus::class)->findAll();

        $admin = new Participant();
        $admin->setNom($faker->lastName());
        $admin->setPrenom($faker->firstName());
        $admin->setPseudo("Admin");
        $admin->setEmail("admin@mail.com");
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'admin'));
        $admin->setAdministrateur(true);
        $admin->setActif(true);
        $admin->setTelephone($faker->regexify('/^0[1-9]\d{8}$/')); //génère un numéro de téléphone à 10 chiffres commencant par 0)
        $admin->setCampus($faker->randomElement($campus));

        $manager->persist($admin);
        $manager->flush();

        for ($i=1;$i<=10;$i++) {
            $participant = new Participant();
            $participant->setNom($faker->lastName());
            $participant->setPseudo($faker->userName());
            $participant->setPrenom($faker->firstName());
            $participant->setEmail($faker->email());
            $participant->setPassword($this->userPasswordHasher->hashPassword($participant, 'mdp'));
            $participant->setAdministrateur(false);
            $participant->setActif(true);
            $participant->setTelephone($faker->regexify('/^0[1-9]\d{8}$/'));
            $participant->setCampus($faker->randomElement($campus));

            $manager->persist($participant);
        }
        $manager->flush();
    }
    public function getDependencies()
    {
        return [CampusFixtures::class];
    }
}
