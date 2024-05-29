<?php

namespace App\Utils;

use App\Entity\Errolda;
use App\Entity\Hauteskundea;
use App\Entity\Herritarra;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

final class CsvHelper
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function importErrolda($csvErrolda): array
    {
        $csvErrolda->move('/tmp', 'errolda.csv');
        $header = null;
        $data = [];
        if (($handle = fopen('/tmp/errolda.csv', 'rb')) !== false) {
            while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        try {
            foreach ($data as $row) {
                $errolda = new Errolda();
                $errolda->setIdent($row['IDENT']);
                $errolda->setDianac($row['DIANAC']);
                $errolda->setMesnac($row['MESNAC']);
                $errolda->setAnonac($row['ANONAC']);
                $this->entityManager->persist($errolda);
            }
        } catch (\Exception $exception) {
            $result = ['result' => 'error'];
            $result['exception'] = $exception->getMessage();
            $result['ident'] = $row['IDENT'] ?? '';
            $nom = $row['NOMBRE'] ?? '';
            $ape1 = $row['APELLIDO1'] ?? '';
            $ape2 = $row['APELLIDO2'] ?? '';
            $result['herritarra'] = $nom.' '.$ape1.' '.$ape2;

            return $result;
        }

        $this->entityManager->flush();

        return ['result' => 'success'];
    }

    public function importZozketa($csvZozketa, $hauteskundeaid): array
    {
        $csvZozketa->move('/tmp', 'zozketa.csv');
        $header = null;
        $data = [];
        if (($handle = fopen('/tmp/zozketa.csv', 'rb')) !== false) {
            while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        $hauteskundea = $this->entityManager->getRepository(Hauteskundea::class)->find($hauteskundeaid);

        try {
            foreach ($data as $row) {
                $herritarra = new Herritarra();
                $herritarra->setHauteskundea($hauteskundea);
                $herritarra->setDist($row['DIST']);
                $herritarra->setSecc($row['SECC']);
                $herritarra->setMesa($row['MESA']);
                $herritarra->setNlocal($row['NLOCAL']);
                $herritarra->setNlocalb($row['NLOCALB']);
                $herritarra->setHelbidea($row['DOMI1']);
                $herritarra->setBarrutia($row['DOMI2']);
                $herritarra->setCargofinal($row['CARGOFINAL']);
                $herritarra->setCargo($this->setKarguaByCode($row['CARGOFINAL'], 'es'));
                $herritarra->setKargua($this->setKarguaByCode($row['CARGOFINAL'], 'eus'));
                $herritarra->setIdent($row['IDENT']);
                $herritarra->setNombre($row['NOMBRE']);
                $herritarra->setApellido1($row['APELLIDO1']);
                $herritarra->setApellido2($row['APELLIDO2']);
                if (!empty($row['Eguna'])) {
                    $eguna = $row['Eguna'];
                    $herritarra->setEguna($eguna);
                }
                if (!empty($row['Hilabetea'])) {
                    $hilabetea = $row['Hilabetea'];
                    $herritarra->setHilabetea($hilabetea);
                }
                if (!empty($row['Urtea'])) {
                    $urtea = $row['Urtea'];
                    $herritarra->setUrtea($urtea);
                }
                if (!empty($row['Eguna']) && !empty($row['Hilabetea']) && !empty($row['Urtea'])) {
                    $jaiteguna = $urtea.'-'.$hilabetea.'-'.$eguna;
                    $herritarra->setJaioteguna(new \DateTime($jaiteguna));
                }

                $herritarra->setActive(in_array($row['CARGOFINAL'], ['P', 'PS1', 'PS2', 'V1', 'V1S1', 'V1S2', 'V2', 'V2S1', 'V2S2']) ? 1 : 0);
                $this->entityManager->persist($herritarra);
            }
        } catch (\Exception $exception) {
            $result = ['result' => 'error'];
            $result['exception'] = $exception->getMessage();
            $result['ident'] = $row['IDENT'] ?? '';
            $nom = $row['NOMBRE'] ?? '';
            $ape1 = $row['APELLIDO1'] ?? '';
            $ape2 = $row['APELLIDO2'] ?? '';
            $result['herritarra'] = $nom.' '.$ape1.' '.$ape2;

            return $result;
        }

        $this->entityManager->flush();

        return ['result' => 'success'];
    }

    public function mergeCsv($hauteskundeaid): array
    {
        try {
            /** @var Hauteskundea $hauteskundea */
            $hauteskundea = $this->entityManager->getRepository(Hauteskundea::class)->find($hauteskundeaid);
            foreach ($hauteskundea->getHerritarrak() as $herritarra) {
                $errolda = $this->entityManager->getRepository(Errolda::class)->findOneBy(['ident' => $herritarra->getIdent()]);
                if (!$errolda) {
                    throw new EntityNotFoundException('Ez da herritarra Erroldan topatu');
                }
                $urtea = $errolda->getAnonac();
                $hilabetea = $errolda->getMesnac();
                $eguna = $errolda->getDianac();
                $mifetxa = \DateTime::createFromFormat('Y-m-d', sprintf('%d-%02d-%02d', $urtea, $hilabetea, $eguna));
                $herritarra->setJaioteguna($mifetxa);
                $this->entityManager->persist($herritarra);
            }
            $this->entityManager->flush();

            return ['result' => 'success'];
        } catch (\Exception $exception) {
            $result = ['result' => 'error'];
            $result['exception'] = $exception->getMessage();
            $result['ident'] = $row['IDENT'] ?? '';
            $nom = $row['NOMBRE'] ?? '';
            $ape1 = $row['APELLIDO1'] ?? '';
            $ape2 = $row['APELLIDO2'] ?? '';
            $result['herritarra'] = $nom.' '.$ape1.' '.$ape2;

            return $result;
        }
    }

    /**
     * @throws Exception
     */
    public function hustuErrolda(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $sql = $platform->getTruncateTableSQL('errolda', true);

        $connection->executeStatement($sql);
    }

    private function setKarguaByCode($code, $lang): ?string
    {
        $positions = [
            ['code' => 'P', 'eus' => 'Presidentea', 'es' => 'Presidente/a'],
            ['code' => 'PS1', 'eus' => 'Presidentea-Lehen ordezkoa', 'es' => 'Presidente/a-Primer/a suplente'],
            ['code' => 'PS2', 'eus' => 'Presidentea-Bigarren ordezkoa', 'es' => 'Presidente/a-Segundo/a suplente'],
            ['code' => 'V1', 'eus' => 'Lehen bokala', 'es' => 'Primer/a vocal'],
            ['code' => 'V1S1', 'eus' => 'Lehen bokala-Lehen ordezkoa', 'es' => 'Primer/a vocal-Primer/a suplente'],
            ['code' => 'V1S2', 'eus' => 'Lehen bokala-Bigarren ordezkoa', 'es' => 'Primer/a vocal-Segundo/a suplente'],
            ['code' => 'V2', 'eus' => 'Bigarren bokala', 'es' => 'Segundo/a vocal'],
            ['code' => 'V2S1', 'eus' => 'Bigarren bokala-Lehen ordezkoa', 'es' => 'Segundo/a vocal-Primer/a suplente'],
            ['code' => 'V2S2', 'eus' => 'Bigarren bokala-Bigarren ordezkoa', 'es' => 'Segundo/a vocal-Segundo/a suplente'],
        ];

        foreach ($positions as $position) {
            if ($position['code'] === $code) {
                return $position[$lang];
            }
        }

        return null; // Return null if code is not found
    }
}
