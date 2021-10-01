<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager)
    {
        // Products
        $brands = [
            "Apple",
            "Samsung",
            "Sony",
            "Huawei",
            "Xiaomi"
        ];
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 25; $i++) {
            $product = new Product();
            $product->setBrand($faker->randomElement($brands));
            $reference = str_repeat($faker->randomLetter, 5);
            $reference .= str_repeat($faker->randomDigit, 5);
            $product->setReference($reference);
            $product->setName($faker->word);
            $product->setColor($faker->colorName);
            $product->setDescription($faker->paragraphs(mt_rand(1, 4), true));
            $product->setSize($faker->randomFloat(1, 3, 10));
            $product->setPrice($faker->randomFloat(2, 150, 1500));
            $manager->persist($product);
        }

        //Companies
        $bileMo = new Company();
        $bileMo
            ->setName("BileMo")
            ->setAddress($faker->streetAddress)
            ->setZip($faker->numberBetween(1000, 99999))
            ->setCity($faker->city)
            ->setCountry("France")
            ->setEmail("contact@bilemo.com")
            ->setPhone($faker->phoneNumber)
            ->setRegistrationDate($faker->dateTimeBetween('-30 days', '-20 days'));

        $admin = new User();
        $admin
            ->setUsername('Admin')
            ->setEmail('admin@bilemo.com')
            ->setFirstName('Jean')
            ->setLastName('Bon')
            ->setRegistrationDate($faker->dateTimeBetween('-20 days', '-18 days'))
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($admin, 'admin'))
        ;

        $bileMo->addUser($admin);

        $manager->persist($bileMo);

        $companies = [
            "MobileHouse",
            "PhoneKings",
            "SuperGSM"
        ];
        foreach ($companies as $companyName) {
            $company = new Company();
            $company
                ->setName($companyName)
                ->setAddress($faker->streetAddress)
                ->setZip($faker->numberBetween(1000, 99999))
                ->setCity($faker->city)
                ->setCountry("France")
                ->setEmail("contact@" . strtolower($companyName) . ".com")
                ->setPhone($faker->phoneNumber)
                ->setRegistrationDate($faker->dateTimeBetween('-17 days'));
            for ($i = 0; $i < 2; $i++) {
                $user = new User();
                $user
                    ->setUsername($companyName . 'User' . $i+1)
                    ->setEmail(strtolower($companyName) . 'user' . $i+1 . '@' . strtolower($companyName) . '.com')
                    ->setFirstName($faker->firstName)
                    ->setLastName($faker->lastName)
                    ->setRegistrationDate($faker->dateTimeBetween('-17 days'))
                    ->setRoles(['ROLE_USER'])
                    ->setPassword($this->passwordHasher->hashPassword($user, 'user'))
                ;
                $company->addUser($user);
            }

            $manager->persist($company);
        }
        $manager->flush();
    }
}
