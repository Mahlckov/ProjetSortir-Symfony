<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà utilisé pour un autre compte")
 * @UniqueEntity(fields={"pseudo"}, message="Ce pseudo est déjà utilisé pour un autre compte")
 */
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(message="L'email est obligatoire")
     * @Assert\Email(message="Votre email n'est pas valide !")
     */
    private $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\Length(
     *     min=3,
     *     minMessage="Minimum 3 caractères s'il vous plait!",
     * )
     */
    private $motPasse;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Minimum 3 caractères s'il vous plait!",
     *     maxMessage="Maximum 50 caractères s'il vous plait!"
     * )
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Le prénom est obligatoire")
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Minimum 3 caractères s'il vous plait!",
     *     maxMessage="Maximum 50 caractères s'il vous plait!"
     * )
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Regex(
     *     pattern="/^0[1-9]\d{8}$/",
     *     message="Votre numéro de téléphone doit commencer par un 0 et comporter 10 chiffres"
     * )
     */
    private $telephone;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $actif;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank(message="Le pseudo est obligatoire!")
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Minimum 3 caractères s'il vous plait!",
     *     maxMessage="Maximum 50 caractères s'il vous plait!"
     * )
     * @Assert\Regex(
     *     pattern="/^[a-z0-9_-]+$/i",
     *     message="Votre pseudo ne doit comporter que des lettres, nombres, underscores et tirets!"
     * )
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomImage;

    /**
     * @ORM\ManyToMany(targetEntity=Sortie::class, inversedBy="participants")
     */
    private $inscriptions;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="organisateur", orphanRemoval=true)
     */
    private $mesSorties;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\OneToMany(targetEntity=Groupe::class, mappedBy="IdCreateur")
     */
    private $groupes;

    public function __construct()
    {
        $this->groupes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
     public function getUserIdentifier(): string
     {
         return (string)$this->email;
     }
    /*
    public function getUserIdentifier(): string
    {
        $identifiant = (string)$this->email;
        if (strpos($identifiant, '@') === false) {
            $identifiant = (string)$this->pseudo;
        }
        return $identifiant;
    }
*/

    /**
     * @see UserInterface
     */

    public function getRoles(): array
    {
        $roles = [];
        if ($this->isAdministrateur()) {
            $roles[] = 'ROLE_ADMIN';
        } else {
            // guarantee every user at least has ROLE_USER
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->motPasse;
    }

    public function setPassword(string $motPasse): self
    {
        $this->motPasse = $motPasse;

        return $this;
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getNomImage(): ?string
    {
        return $this->nomImage;
    }

    public function setNomImage(?string $nomImage): self
    {
        $this->nomImage = $nomImage;

        return $this;
    }
    /**
     * @return Collection<int, Sortie>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Sortie $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions[] = $inscription;
        }

        return $this;
    }

    public function removeInscription(Sortie $inscription): self
    {
        $this->inscriptions->removeElement($inscription);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getMesSorties(): Collection
    {
        return $this->mesSorties;
    }

    public function addMesSorty(Sortie $mesSorty): self
    {
        if (!$this->mesSorties->contains($mesSorty)) {
            $this->mesSorties[] = $mesSorty;
            $mesSorty->setOrganisateur($this);
        }

        return $this;
    }

    public function removeMesSorty(Sortie $mesSorty): self
    {
        if ($this->mesSorties->removeElement($mesSorty)) {
            // set the owning side to null (unless already changed)
            if ($mesSorty->getOrganisateur() === $this) {
                $mesSorty->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }

    public function getUsername()
    {
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes[] = $groupe;
            $groupe->setIdCreateur($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        if ($this->groupes->removeElement($groupe)) {
            // set the owning side to null (unless already changed)
            if ($groupe->getIdCreateur() === $this) {
                $groupe->setIdCreateur(null);
            }
        }

        return $this;
    }


}

