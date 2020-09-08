<?php
namespace App\Document;

use ApiPlatform\Core\Annotation\ApiProperty;

class Credentials
{
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
	 */
    private $login;

	/**
	 * @ApiProperty(
	 *     iri="https://schema.org/accessCode",
	 *     attributes={
	 *     		"openapi_context"={
	 *     			"type"="string"
	 *     		}
	 *	 	}
	 *	 )
	 */
    private $password;

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
	 */
    private $rememberMe;

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): ?self
    {
      $this->login = $login;
      return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): ?self
    {
      $this->password = $password;
      return $this;
    }

    public function getRememberMe(): ?bool
    {
        return $this->password;
    }

    public function setRememberMe(bool $rememberMe): ?self
    {
        $this->rememberMe = $rememberMe;
        return $this;
    }

}
