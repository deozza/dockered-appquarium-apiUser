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
 *          "get"={
 *     			"openapi_context"={
 *     				"summary"="List",
 *     				"description"="Get the list of registered users"
 * 				}
 *	 		},
 *          "post"={
 *     			"normalization_context"={"groups"={"user:read"}},
 *     			"validation_groups"={"Default", "postValidation"},
 *     			"openapi_context"={
 *     				"summary"="Register",
 *     				"description"="Register as a new user"
 * 				}
 *	 		},
 *          "get_current"={
 *     			"route_name"="api_get_users_current",
 *     			"method"="GET",
 *     			"normalization_context"={"groups"={"user:read"}},
 *     			"openapi_context"={
 *     				"summary"="Current profile",
 *     				"description"="Get current logged in user profile"
 * 				}
 *	 		},
 *     		"patch_current"={
 *     			"route_name"="api_patch_users_current",
 *     			"method"="PATCH",
 *     			"path"="/api/users/current",
 *     			"normalization_context"={"groups"={"user:read"}},
 *     			"denormalization_context"={"groups"={"user:update"}},
 *     			"openapi_context"={
 *     				"summary"="Profile",
 *     				"description"="Update the current logged in user profile"
 * 				}
 *	 		},
 *     		"patch_current_password"={
 *     			"route_name"="api_patch_users_current_password",
 *     			"method"="PATCH",
 *     			"path"="/api/users/current/password",
 *     			"normalization_context"={"groups"={"user:read"}},
 *     			"denormalization_context"={"groups"={"user:update:password"}},
 *     			"openapi_context"={
 *     				"summary"="Password",
 *     				"description"="Update the current logged in user password"
 * 				}
 *	 		}
 *     },
 *     itemOperations={
 *          "get"={
 *     			"requirements"={"id"="\d+"},
 *     			"openapi_context"={
 *     				"summary"="Specific profile",
 *     				"description"="Get the profile of a specific user"
 * 				}
 * 			},
 *          "patch"={
 *     			"requirements"={"id"="\d+"},
 *     			"denormalization_context"={"groups"={"user:admin:update", "user:update"}, "allow_extra_attributes"=false},
 *     			"openapi_context"={
 *     				"summary"="Specific profile",
 *     				"description"="Patch the profile of a specific user"
 * 				}
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
	 * @ApiProperty(
	 *     iri="https://schema.org/identifier",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="integer",
	 *     			"example"="1"
	 *     		}
	 *	 	}
	 *	 )
     * @ODM\Id(strategy="INCREMENT", type="integer")
     * @Groups({"user:read:admin"})
     */
    protected $id;

    /**
     * @ApiProperty(
	 *     iri="https://schema.org/name",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="string",
	 *     			"example"="johndoe"
	 *     		}
	 *	 	}
	 *	 )
     * @ODM\Field(type="string")
     * @Groups({"user:read", "user:write", "user:update"})
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ApiProperty(
	 *     iri="https://schema.org/email",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="email",
	 *     			"example"="johndoe@mail.com"
	 *     		}
	 *	 	}
	 *	 )
     * @ODM\Field(type="string")
     * @Groups({"user:read", "user:write", "user:update"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/accessCode",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="string"
	 *     		}
	 *	 	}
	 *	 )
	 * @Groups({"user:update", "user:update:password"})
     * @SerializedName("password")
     */
    private $plainPassword;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/accessCode",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="string"
	 *     		}
	 *	 	}
	 *	 )
	 * @Groups({"user:write", "user:update", "user:update:password"})
	 * @Assert\NotBlank(groups={"postValidation"})
     * @SerializedName("password")
     */
    private $newPassword;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/accessCode",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="string"
	 *     		}
	 *	 	}
	 *	 )
     * @Groups({"user:write", "user:update", "user:update:password"})
	 * @Assert\NotBlank(groups={"postValidation"})
     * @Assert\EqualTo(
     *     propertyPath="newPassword",
     *     message="This value should be equal to password field."
     * )
     */
    private $repeatPassword;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/accessCode",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="string"
	 *     		}
	 *	 	}
	 *	 )
     * @ODM\Field(type="string")
     */
    private $password;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/DateTime",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="\DateTime",
	 *     			"example"="2020-01-01 12:30:00"
	 *     		}
	 *	 	}
	 *	 )
     * @ODM\Field(type="date")
     * @Groups({"user:read"})
     */
    private $lastLogin;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/DateTime",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="\DateTime",
	 *     			"example"="2020-01-01 12:30:00"
	 *     		}
	 *	 	}
	 *	 )
     * @ODM\Field(type="date")
     * @Groups({"user:read:admin"})
     */
    private $lastFailedLogin;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/dateCreated",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="\DateTime",
	 *     			"example"="2020-01-01 12:30:00"
	 *     		}
	 *	 	}
	 *	 )
	 *
	 * @Groups({"user:read"})
     * @ODM\Field(type="date")
     */
    private $registerDate;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/Boolean",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="boolean",
	 *     			"example"="true"
	 *     		}
	 *	 	}
	 *	 )
	 * @Groups({"user:read:admin", "user:admin:update"})
     * @ODM\Field(type="boolean")
     */
    private $active;

    /**
	 * @ApiProperty(
	 *     iri="https://schema.org/OrganizationRole",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="array",
	 *     			"example"="[ROLE_USER]"
	 *     		}
	 *	 	}
	 *	 )
	 * @Groups({"user:read:admin", "user:admin:update", "user:read"})
     * @ODM\Field(type="collection")
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