<?php
namespace App\Document;

class Credentials
{
    private $login;
    private $password;
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

    public function setRememberMe($rememberMe): ?self
    {
        $this->rememberMe = $rememberMe;
        return $this;
    }

}
