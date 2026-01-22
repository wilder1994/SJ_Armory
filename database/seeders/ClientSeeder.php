<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Seguridad Andina SAS',
                'nit' => '900123456-1',
                'legal_representative' => 'Luis Alberto Ramirez',
                'contact_name' => 'Diana Cardona',
                'email' => 'contacto@seguridadandina.com',
                'address' => 'Calle 72 # 10-45',
                'neighborhood' => 'Chapinero',
                'city' => 'Bogota',
            ],
            [
                'name' => 'Vigilancia Caribe LTDA',
                'nit' => '800987654-3',
                'legal_representative' => 'Carlos Andres Diaz',
                'contact_name' => 'Maria Fernanda Ruiz',
                'email' => 'info@vigilanciacaribe.com',
                'address' => 'Carrera 53 # 84-120',
                'neighborhood' => 'Riomar',
                'city' => 'Barranquilla',
            ],
            [
                'name' => 'Proteccion del Valle SAS',
                'nit' => '901234567-8',
                'legal_representative' => 'Jorge Ivan Herrera',
                'contact_name' => 'Natalia Ortiz',
                'email' => 'contacto@protecciondelvalle.com',
                'address' => 'Avenida 6N # 23-60',
                'neighborhood' => 'Granada',
                'city' => 'Cali',
            ],
            [
                'name' => 'Seguridad Metropolitana SAS',
                'nit' => '890123987-5',
                'legal_representative' => 'Ana Lucia Moreno',
                'contact_name' => 'Felipe Castro',
                'email' => 'servicio@segmetropolitana.com',
                'address' => 'Calle 10 # 43-20',
                'neighborhood' => 'El Poblado',
                'city' => 'Medellin',
            ],
            [
                'name' => 'Custodia Santander SAS',
                'nit' => '830456789-2',
                'legal_representative' => 'Ricardo Gomez',
                'contact_name' => 'Carolina Vega',
                'email' => 'contacto@custodiasantander.com',
                'address' => 'Carrera 33 # 45-18',
                'neighborhood' => 'Cabecera',
                'city' => 'Bucaramanga',
            ],
        ];

        foreach ($clients as $client) {
            Client::updateOrCreate(
                ['nit' => $client['nit']],
                $client
            );
        }
    }
}
