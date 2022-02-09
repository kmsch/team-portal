<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;
use TeamPortal\Entities\Bardienstmail;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Samenvattingsmail;
use TeamPortal\Entities\Scheidsrechtersmail;
use TeamPortal\Entities\Tellersmail;
use TeamPortal\Entities\Zaalwachtmail;
use TeamPortal\Entities\Zaalwachttype;

class QueueWeeklyEmails implements Interactor
{
    private Persoon $scheidsco;
    private array $webcieMembers;

    public function __construct(
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\ZaalwachtGateway $zaalwachtGateway,
        Gateways\EmailGateway $emailGateway,
        Gateways\BarcieGateway $barcieGateway,
        Gateways\JoomlaGateway $joomlaGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->mailQueueGateway = $emailGateway;
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $this->scheidsco = $this->joomlaGateway->GetUser(2573); // scheidsco-ID
        $this->webcieMembers = [
            $this->joomlaGateway->GetUser(542),  // Sjon
            $this->joomlaGateway->GetUser(2036), // Banda
            $this->joomlaGateway->GetUser(2212)  // Bas
        ];

        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 7);
        foreach ($wedstrijddagen as $dag) {
            $bardag = $this->barcieGateway->GetBardag($dag->date);
            $dag->barshifts = $bardag->shifts;
            $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($dag->date);
            if ($zaalwacht) {
                if ($zaalwacht->eersteZaalwacht) {
                    $dag->eersteZaalwacht = $zaalwacht->eersteZaalwacht;
                    $dag->eersteZaalwacht->teamgenoten = $this->joomlaGateway->GetTeamgenoten($dag->eersteZaalwacht);
                }

                if ($zaalwacht->tweedeZaalwacht) {
                    $dag->tweedeZaalwacht = $zaalwacht->tweedeZaalwacht;
                    $dag->tweedeZaalwacht->teamgenoten = $this->joomlaGateway->GetTeamgenoten($dag->tweedeZaalwacht);
                }
            }
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $fluitwedstrijd = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
                    $wedstrijd->scheidsrechter = $fluitwedstrijd->scheidsrechter;
                    $wedstrijd->tellers = $fluitwedstrijd->tellers;
                }
            }
        }

        $emails = $this->GetAllEmails($wedstrijddagen);

        $this->mailQueueGateway->QueueEmails($emails);
    }

    private function GetAllEmails(array $wedstrijdagen): array
    {
        $emails = [];

        $samenvatting = new Emailsamenvatting();
        foreach ($wedstrijdagen as $dag) {
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    if ($wedstrijd->scheidsrechter) {
                        $mail = new Scheidsrechtersmail($wedstrijd, $this->scheidsco);
                        $emails[] = $mail;
                        $samenvatting->scheidsrechters[] = $wedstrijd->scheidsrechter;
                    }

                    if ($wedstrijd->tellers[0]) {
                        $mails[] = new Tellersmail($wedstrijd, $wedstrijd->tellers[0], $this->scheidsco);
                        $samenvatting->tellers[] = $wedstrijd->tellers[0];
                    }

                    if ($wedstrijd->tellers[1]) {
                        $emails[] = new Tellersmail($wedstrijd, $wedstrijd->tellers[1], $this->scheidsco);
                        $samenvatting->tellers[] = $wedstrijd->tellers[1];
                    }
                }
            }

            if ($dag->eersteZaalwacht) {
                foreach ($dag->eersteZaalwacht->teamgenoten as $teamgenoot) {
                    $emails[] = new Zaalwachtmail($dag, $teamgenoot, $this->scheidsco, Zaalwachttype::EersteZaalwacht);
                }
                $samenvatting->zaalwachtteams[] = $dag->eersteZaalwacht;
            }

            if ($dag->tweedeZaalwacht) {
                foreach ($dag->tweedeZaalwacht->teamgenoten as $teamgenoot) {
                    $emails[] = new Zaalwachtmail($dag, $teamgenoot, $this->scheidsco, Zaalwachttype::TweedeZaalwacht);
                }
                $samenvatting->zaalwachtteams[] = $dag->tweedeZaalwacht;
            }

            foreach ($dag->barshifts as $barshift) {
                foreach ($barshift->barleden as $barlid) {
                    $emails[] = new Bardienstmail($barlid, $this->scheidsco, $dag->date);
                    $samenvatting->barleden[] = $barlid;
                }
            }
        }

        $emails[] = new Samenvattingsmail($samenvatting, $this->scheidsco);
        foreach ($this->webcieMembers as $webcieMember) {
            $emails[] = new Samenvattingsmail($samenvatting, $webcieMember);
        }

        return $emails;
    }
}
