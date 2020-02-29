<?php
/*
 * This code was developed by NIMA SOFTWARE SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */


namespace App\Services\User;


use App\County;
use App\Institution;
use App\Organisation;
use App\User;
use Doctrine\CouchDB\CouchDBException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Import
{

    private $rolesList = [];

    private $countiesMap = [];

    private $errors = [];

    private $citiesMap = [];

    private $usersCreated = [];

    public function __construct()
    {
        $this->setup();
    }

    public function setup()
    {
        $this->rolesList = array_values(config('roles.role'));
        $this->prepareCountiesMap();
    }

    protected function prepareCountiesMap()
    {
        /** @var \Illuminate\Database\Eloquent\Collection $countys */
        $counties = County::all(['_id', "slug", "name"]);
        $this->countiesMap = array_column($counties->toArray(), null, 'slug');
    }

    public function importRescueOfficers(iterable $list, User $importedBy)
    {
        return $this->import($list, User::ROLE_RESCUE_OFFICER, $importedBy);
    }

    public function import(iterable $dataset, int $userRole, User $importedBy): self
    {
        if (!in_array($userRole, $this->rolesList)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid user type! Must be one of  %s you provided %s',
                    print_r($this->rolesList, true),
                    $userRole
                )
            );
        }


        /*
         * array(7) {
                  [0]=> "test popescu"
                  [1]=> "asfa@fas.asd"
                  [2]=> "Iasi"
                  [3]=> "Tg. frumos"
                  [4]=> "123123"
                  [5]=> "institutie slug"
                  [6]=> "organizatie slug"
                }
         */
        /** @var array $item */
        foreach ($dataset as $item) {

            $county = $this->getCounty($item[2]);

            $city = $this->getCity($item[3], $county);

            $userData = [
                'name' => $item[0],
                'email' => $item[1],
                'county' => $county,
                'city' => $city,
                'password' => Hash::make(Str::random(16)),
                'role' => $userRole,
                'phone' => $item[4],
                'added_by' => $importedBy->_id,
            ];

            $institution = $this->getInstitution($item[5], $importedBy);
            if ($institution) {
                $userData['institution'] = $institution;
            }

            $organisation = $this->getOrganisation($item[6], $importedBy);
            if ($organisation) {
                $userData['organisation'] = $organisation;
            }

            if ($this->validateUser($userData)) {
                /*
                 * putem face asta intr-un batch insert ?
                 */
                $user = User::create($userData);

                $this->usersCreated[] = $user;
            }

        }

        return $this;
    }

    protected function getCounty(string $slug): ?array
    {
        $countySlug = removeDiacritics($slug);

        if (!isset($this->countiesMap[$countySlug])) {
            $this->errors = addError($this->errors, $slug, 'Judetul nu exista');

            return null;
        }

        return $this->countiesMap[$countySlug];
    }

    protected function getCity(string $citySlug, array $county): ?array
    {
        $citySlug = removeDiacritics($citySlug);

        if (isset($this->citiesMap[$citySlug])) {
            return $this->citiesMap[$citySlug];
        }


        try {
            /**
             * this should be cached
             * - locally
             * - memcached, redis
             * -second level cache in ORM ?
             */
            $getCity = \DB::connection('statics')->getCouchDBClient()
                ->createViewQuery('cities', 'slug')
                ->setKey([$county['_id'], $citySlug])
                ->execute();

        } catch (CouchDBException $e) {

            Log::error(
                sprintf(
                    'An error occurred while searching a city in import users service. We search for citySlug: %s and county %s',
                    $citySlug,
                    serialize($county)
                )
            );
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return null;
        }

        if (!$getCity->offsetExists(0)) {
            $this->errors = addError($this->errors, $citySlug, 'Orasul nu exista');

            return null;
        }

        $city = $getCity->offsetGet(0);

        //local cache
        $this->citiesMap[$city[$citySlug]] = $city;

        return [
            "_id" => $city['id'],
            "name" => $city['value'],
        ];

    }

    protected function getInstitution(?string $slug, User $importedBy): ?array
    {
        if (!$slug) {
            return $importedBy->institution;
        }

        $institution = Institution::where('slug', '=', $slug)->first();

        if (!$institution) {
            $this->errors = addError($this->errors, $slug, 'Institutia nu exista');

            return null;
        }

        /*
        * ne trebuie ceva sa verificam daca $importedBy are voie sa faca import pe aceasta institutie
        */

        return ['_id' => $institution->_id, 'name' => $institution->name];
    }

    protected function getOrganisation(string $slug, User $importedBy): ?array
    {

        if (!$slug) {
            return null;
        }

        $organisation = Organisation::where('slug', '=', $slug)->first();

        if (!$organisation) {
            $this->errors = addError($this->errors, $slug, 'Organizatia nu exista');

            return null;
        }

        /*
         * ne trebuie ceva sa verificam daca $importedBy are voie sa faca import pe aceasta organizatie
         */

        return ['_id' => $organisation->_id, 'name' => $organisation->name];
    }

    protected function validateUser(array $userData)
    {
        //taken from UserController::store()
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users.users',
            'role' => 'required',
            'phone' => 'required|string|min:6|',
        ];

        $validator = Validator::make($userData, $rules);
        if ($validator->fails()) {

            foreach ($validator->errors()->messages() as $key => $error) {

                $this->errors = addError($this->errors, $key, $error);
            }

            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getTotalImported(): int
    {
        return count($this->usersCreated);
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getCreatedUsers(): array
    {
        return $this->usersCreated;
    }

}