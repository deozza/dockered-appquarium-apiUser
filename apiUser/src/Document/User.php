<?php

namespace App\Document;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\OrderFilter;

use App\Validator\UniqueEmail;
use App\Validator\UniqueUsername;
use App\Repository\UserRepository;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get"={},
 *          "post"={
 *     			"normalization_context"={"groups"={"user:read"}},
 *     			"validation_groups"={"Default", "postValidation"}
 *	 		},
 *          "get_current"={
 *     			"route_name"="api_get_users_current",
 *     			"method"="GET",
 *     			"normalization_context"={"groups"={"user:read"}}
 *	 		},
 *     		"patch_current"={
 *     			"route_name"="api_patch_users_current",
 *     			"method"="PATCH",
 *     			"path"="/api/users/current",
 *     			"normalization_context"={"groups"={"user:read"}},
 *     			"denormalization_context"={"groups"={"user:update"}}
 *	 		},
 *     		"patch_current_password"={
 *     			"route_name"="api_patch_users_current_password",
 *     			"method"="PATCH",
 *     			"path"="/api/users/current/password",
 *     			"normalization_context"={"groups"={"user:read"}},
 *     			"denormalization_context"={"groups"={"user:update"}}
 *	 		}
 *     },
 *     itemOperations={
 *          "get"={
 *     			"requirements"={"id"="\d+"}
 * 			},
 *          "patch"={
 *     			"requirements"={"id"="\d+"},
 *     			"denormalization_context"={"groups"={"user:admin:update", "user:update"}, "allow_extra_attributes"=false}
 *	 		}
 *     },
 *     normalizationContext={
 *     		"groups"={"user:read", "user:read:admin"},
 *     		"swagger_definition_name"="Read"
 * 	   },
 *     denormalizationContext={
 *     		"groups"={"user:write"},
 *     		"swagger_definition_name"="Write",
 *     		 "allow_extra_attributes"=false
 *	   }
 * )
 * @ODM\Document(repositoryClass=UserRepository::class)
 * @UniqueUsername()
 * @UniqueEmail()
 *
 * @ApiFilter(SearchFilter::class,
 *      properties={
 *     		"username": "ipartial",
 *      	"email": "ipartial"
 * 		}
 *	 )
 * @ApiFilter(BooleanFilter::class,
 *      properties={
 *     		"active"
 * 		}
 *	 )
 * @ApiFilter(OrderFilter::class,
 *     properties={
 *     		"username",
 *     		"email",
 *     		"lastLogin",
 *     		"registerDate"
 * 		},
 *      arguments={
 *     		"orderParameterName"="order"
 * 		}
 *	 )
 *
 * @ODM\PostPersist({
 *     "App\EventListener\User\SendRegistrationEmail",
 *	 })
 */
class User implements UserInterface
{
    /**
     * @ODM\Id(strategy="INCREMENT", type="integer")
     * @Groups({"user:read:admin"})
     */
    protected $id;

    /**
     * @ApiProperty(iri="https://schema.org/name")
     * @ODM\Field(type="string")
     * @Groups({"user:read", "user:write", "user:update"})
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ApiProperty(iri="https://schema.org/email")
     * @ODM\Field(type="string")
     * @Groups({"user:read", "user:write", "user:update"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @Groups({"user:update"})
     * @SerializedName("password")
     */
    private $plainPassword;

    /**
     * @Groups({"user:write", "user:update"})
	 * @Assert\NotBlank(groups={"postValidation"})
     * @SerializedName("password")
     */
    private $newPassword;

    /**
     * @Groups({"user:write", "user:update"})
	 * @Assert\NotBlank(groups={"postValidation"})
     * @Assert\EqualTo(
     *     propertyPath="newPassword",
     *     message="This value should be equal to password field."
     * )
     */
    private $repeatPassword;

    /**
     * @ODM\Field(type="string")
     */
    private $password;

    /**
     * @ODM\Field(type="date")
     * @Groups({"user:read"})
     */
    private $lastLogin;

    /**
     * @ODM\Field(type="date")
     * @Groups({"user:read:admin"})
     */
    private $lastFailedLogin;

    /**
     * @ApiProperty(iri="https://schema.org/dateCreated")
     * @ODM\Field(type="date")
     * @Groups({"user:read"})
     */
    private $registerDate;

    /**
     * @ODM\Field(type="boolean")
     * @Groups({"user:read:admin", "user:admin:update"})
     */
    private $active;

    /**
     * @ODM\Field(type="collection")
     * @Groups({"user:read:admin", "user:admin:update", "user:read"})
	 */
    private $roles = [];

    public function __construct()
    {
        $this->active = false;
        $this->registerDate = new \DateTime('now');
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function getRepeatPassword(): ?string
    {
        return $this->repeatPassword;
    }

    public function setRepeatPassword(string $repeatPassword): self
    {
        $this->repeatPassword = $repeatPassword;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
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

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function getLastFailedLogin(): ?\DateTimeInterface
    {
        return $this->lastFailedLogin;
    }

    public function setLastFailedLogin(?\DateTimeInterface $lastFailedLogin): self
    {
        $this->lastFailedLogin = $lastFailedLogin;
        return $this;
    }

    public function getRegisterDate(): ?\DateTimeInterface
    {
        return $this->registerDate;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getRoles() :array
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
        $this->newPassword = null;
    }

	public static function loadRoles()
	{
		return ['ROLE_ADMIN','ROLE_FISH_EDITOR','ROLE_PLANT_EDITOR', 'ROLE_INVERTEBRATE_EDITOR'];
	}
}