<?php

include_once 'NevoboGateway.php';

class GespeeldeWedstrijdenGateway
{
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetGespeeldeWedstrijden()
    {
        $query = 'SELECT * FROM DWF_wedstrijden';
        return $this->database->Execute($query);
    }

    public function AddWedstrijd($wedstrijd)
    {
        $query = 'INSERT INTO DWF_wedstrijden (id, team1, team2, setsTeam1, setsTeam2)
                  VALUES (:id, :team1, :team2, :setsTeam1, :setsTeam2)';
        $params = [
            new Param(':id', $wedstrijd->id, PDO::PARAM_STR),
            new Param(':team1', $wedstrijd->team1, PDO::PARAM_STR),
            new Param(':team2', $wedstrijd->team2, PDO::PARAM_STR),
            new Param(':setsTeam1', $wedstrijd->setsTeam1, PDO::PARAM_INT),
            new Param(':setsTeam2', $wedstrijd->setsTeam2, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    public function AddPunt($wedstrijd, $set, $opstelling, $isThuisService, $isThuisPunt, $puntenTeam1, $puntenTeam2)
    {
        $query = 'INSERT INTO DWF_punten (matchId, currentSet, isThuisService, isThuisPunt, puntenTeam1, puntenTeam2, ra, rv, mv, lv, la, ma)
                  VALUES (:matchId, :currentSet, :isThuisService, :isThuisPunt, :puntenTeam1, :puntenTeam2, :ra, :rv, :mv, :lv, :la, :ma)';
        $params = [
            new Param(Column::MatchId, $wedstrijd->id, PDO::PARAM_STR),
            new Param(':currentSet', $set, PDO::PARAM_INT),
            new Param(':isThuisService', $isThuisService ? 'Y' : 'N', PDO::PARAM_STR),
            new Param(':isThuisPunt', $isThuisPunt ? 'Y' : 'N', PDO::PARAM_STR),
            new Param(':puntenTeam1', $puntenTeam1, PDO::PARAM_STR),
            new Param(':puntenTeam2', $puntenTeam2, PDO::PARAM_STR),
            new Param(':ra', $opstelling[0], PDO::PARAM_INT),
            new Param(':rv', $opstelling[1], PDO::PARAM_INT),
            new Param(':mv', $opstelling[2], PDO::PARAM_INT),
            new Param(':lv', $opstelling[3], PDO::PARAM_INT),
            new Param(':la', $opstelling[4], PDO::PARAM_INT),
            new Param(':ma', $opstelling[5], PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }
}
