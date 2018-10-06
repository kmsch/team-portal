<?php

include 'Database.php';
include 'Utilities.php';

class TeamPortal
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public function NoAction()
    {
        InternalServerError("No action specified");
    }

    public function UnknownAction($action)
    {
        InternalServerError("Unknown function '$action'");
    }

    public function GetMijnOverzicht()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'MijnOverzicht' . DIRECTORY_SEPARATOR . 'GetMijnOverzicht.php';
        $interactor = new GetMijnOverzichtInteractor($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdAanwezigheid()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'GetWedstrijdAanwezigheid.php';
        $interactor = new GetWedstrijdAanwezigheid($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdOverzicht()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'WedstrijdOverzicht' . DIRECTORY_SEPARATOR . 'GetWedstrijdOverzicht.php';
        $interactor = new GetWedstrijdOverzicht($this->database);
        $interactor->Execute();
    }

    public function UpdateAanwezigheid()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'UpdateAanwezigheid.php';
        $interactor = new UpdateAanwezigheid($this->database);
        $postData = $this->GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetFluitOverzicht()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'FluitBeschikbaarheid' . DIRECTORY_SEPARATOR . 'GetFluitBeschikbaarheid.php';
        $interactor = new GetFluitBeschikbaarheid($this->database);
        $interactor->Execute();
    }

    public function UpdateFluitBeschikbaarheid()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'FluitBeschikbaarheid' . DIRECTORY_SEPARATOR . 'UpdateFluitBeschikbaarheid.php';
        $interactor = new UpdateFluitBeschikbaarheid($this->database);
        $postData = $this->GetPostValues();
        $interactor->Execute($postData);
    }

    public function Login()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'Inloggen' . DIRECTORY_SEPARATOR . 'Inloggen.php';
        $interactor = new Inloggen($this->database);
        $postData = $this->GetPostValues();
        $interactor->Execute($postData);
    }

    private function GetPostValues()
    {
        $postData = file_get_contents("php://input");
        if (empty($postData)) {
            return null;
        }

        return json_decode($postData);
    }
}
