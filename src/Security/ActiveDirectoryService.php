<?php

namespace App\Security;

class ActiveDirectoryService
    {
        private $host;
        private $port;
        private $baseDn;

        public function __construct(string $host, int $port, string $baseDn)
        {
            $this->host = $host;
            $this->port = $port;
            $this->baseDn = $baseDn;
            
        }

        public function authenticate(string $username, string $password): bool
        {              
            $ldapConn = ldap_connect($this->host, $this->port);
            if (!$ldapConn) {
                throw new \Exception('Impossible de se connecter au serveur LDAP');
            }

            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            $ldapRdn = $username.'@bcm.int';
            $ldapBind = @ldap_bind($ldapConn, $ldapRdn, $password);
            
            if ($ldapBind) {
                ldap_unbind($ldapConn);
                return true;
            }

            ldap_unbind($ldapConn);
            return false;
        }

            /**
            * Récupère les attributs de l'utilisateur depuis Active Directory
            * 
            * @param string $username Matricule de l'utilisateur
            * @param string $password Mot de passe (pour l'authentification LDAP)
            * @return array Tableau avec les attributs de l'utilisateur
            */


        /*
        public function getUserInfo(string $username, string $password): string
        {
            $ldapConn = ldap_connect($this->host, $this->port);
            if (!$ldapConn) {
                throw new \RuntimeException('Impossible de se connecter au serveur LDAP');
            }

            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            try {
                // 1. Debug: Afficher les paramètres de connexion
                dump([
                    'host' => $this->host,
                    'port' => $this->port,
                    'baseDn' => $this->baseDn,
                    'username' => $username,
                    'domain' => "bcm.int"
                ]);

                // 2. Authentification
                $ldapRdn = $username.'@bcm.int';
                if (!@ldap_bind($ldapConn, $ldapRdn, $password)) {
                    throw new \RuntimeException('Échec de la liaison LDAP: '.ldap_error($ldapConn));
                }

                // 3. Debug: Vérifier la structure LDAP
                $searchTest = @ldap_search($ldapConn, "", "(objectClass=*)", ['namingContexts']);
                if ($searchTest) {
                    $entries = ldap_get_entries($ldapConn, $searchTest);
                    dump(['namingContexts' => $entries]);
                }

                // 4. Recherche de l'utilisateur
                dump("Recherche avec baseDn: ".$this->baseDn);
                $search = ldap_search(
                    $ldapConn, 
                    $this->baseDn, 
                    "(sAMAccountName=$username)",
                    ['displayName']
                );
                
                if ($search === false) {
                    $ldapError = ldap_error($ldapConn);
                    $ldapErrNo = ldap_errno($ldapConn);
                    throw new \RuntimeException("Erreur LDAP [$ldapErrNo]: $ldapError");
                }
                
                $entries = ldap_get_entries($ldapConn, $search);
                dump(['entries' => $entries]);
                exit();
                
                return $entries[0]['displayname'][0] ?? $username;
            } finally {
                if ($ldapConn) {
                    ldap_unbind($ldapConn);
                }
            }
        }

        public function debugLdapConnection(string $username, string $password): array
        {
            $ldapConn = ldap_connect($this->host, $this->port);
            if (!$ldapConn) {
                return ['error' => 'Échec de la connexion au serveur LDAP'];
            }

            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            try {
                // 1. Test d'authentification
                $ldapRdn = $username.'@bcm.int';
                if (!@ldap_bind($ldapConn, $ldapRdn, $password)) {
                    return ['error' => 'Échec de l\'authentification: '.ldap_error($ldapConn)];
                }

                // 2. Test de recherche racine
                $rootDse = @ldap_read($ldapConn, '', '(objectClass=*)', ['namingContexts']);
                if (!$rootDse) {
                    return ['error' => 'Échec de la lecture racine: '.ldap_error($ldapConn)];
                }

                $rootData = ldap_get_entries($ldapConn, $rootDse);
                $namingContexts = $rootData[0]['namingcontexts'] ?? [];

                // 3. Test avec différents contextes de nommage
                $results = [];
                foreach ($namingContexts as $context) {
                    $search = @ldap_search(
                        $ldapConn,
                        $context,
                        "(sAMAccountName=$username)",
                        ['displayName'],
                        0,
                        1
                    );

                    $results[$context] = $search ? 
                        ldap_get_entries($ldapConn, $search) : 
                        ldap_error($ldapConn);
                }

                return [
                    'server' => "$this->host:$this->port",
                    'authenticated_as' => $ldapRdn,
                    'naming_contexts' => $namingContexts,
                    'search_results' => $results,
                    'current_baseDn' => $this->baseDn,
                    'suggestions' => $this->generateDnSuggestions($namingContexts)
                ];

            } finally {
                if ($ldapConn) ldap_unbind($ldapConn);
            }
        }

        private function generateDnSuggestions(array $contexts): array
        {
            $suggestions = [];
            foreach ($contexts as $context) {
                $parts = explode(',', $context);
                
                // Suggestions communes
                $suggestions[] = "OU=Users,$context";
                $suggestions[] = "CN=Users,$context";
                $suggestions[] = "OU=Utilisateurs,$context";
                $suggestions[] = "OU=Employes,$context";
            }
            return $suggestions;
        }

        public function listLdapAttributes(string $username, string $password): array
        {
            $ldapConn = ldap_connect($this->host, $this->port);
            if (!$ldapConn) {
                throw new \RuntimeException('Connexion LDAP impossible');
            }

            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            try {
                // Authentification
                $ldapRdn = $username.'@bcm.int';
                if (!@ldap_bind($ldapConn, $ldapRdn, $password)) {
                    throw new \RuntimeException('Authentification échouée: '.ldap_error($ldapConn));
                }

                // Recherche de l'utilisateur avec tous les attributs
                $search = ldap_search(
                    $ldapConn,
                    'DC=bcm,DC=int',  // DN racine
                    "(sAMAccountName=$username)",
                    ['*']  // Récupère tous les attributs
                );
                
                if (!$search) {
                    throw new \RuntimeException('Recherche échouée: '.ldap_error($ldapConn));
                }
                
                $entries = ldap_get_entries($ldapConn, $search);
                
                if ($entries['count'] == 0) {
                    throw new \RuntimeException('Utilisateur non trouvé');
                }
                
                // Récupère tous les attributs disponibles
                $userEntry = $entries[0];
                $attributes = [];
                
                foreach ($userEntry as $attr => $value) {
                    if (!is_int($attr)) {  // Ignore les métadonnées (comme 'count')
                        $attributes[$attr] = $value;
                    }
                }
                
                return $attributes;
                
            } finally {
                if ($ldapConn) ldap_unbind($ldapConn);
            }
        }
        */

        public function getUserAttributes(string $username, string $password): array
        {
            $ldapConn = ldap_connect($this->host, $this->port);
            if (!$ldapConn) {
                throw new \RuntimeException('Connexion LDAP impossible');
            }

            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            $result = [
                'givenName' => null,
                // 'sn' => null,              // Nom de famille
                // 'displayName' => null,     // Nom complet
                // 'mail' => null,            // Email
                // 'telephoneNumber' => null, // Téléphone
                // 'department' => null,      // Département
                // 'title' => null,           // Titre/Poste
                ];

            try {
                // Authentification, peux ajouter des attribut si besoin
                $ldapRdn = $username.'@bcm.int';
                if (!@ldap_bind($ldapConn, $ldapRdn, $password)) {
                    throw new \RuntimeException('Authentification LDAP échouée: '.ldap_error($ldapConn));
                }

                // Recherche, peux ajouter des attribut si besoin
                $search = ldap_search(
                    $ldapConn,
                    'DC=bcm,DC=int',
                    "(sAMAccountName=$username)",
                    ['givenName']
                    // ['givenName', 'sn', 'displayName', 'mail', 'telephoneNumber', 'department', 'title']
                );
                
                if (!$search) {
                    return $result;
                }
                
                $entries = ldap_get_entries($ldapConn, $search);
                
                if ($entries['count'] === 0) {
                    return $result;
                }
                
                // Assignation explicite pour éviter les problèmes d'inférence
                if (isset($entries[0]['givenname'][0])) {
                    $result['givenName'] = (string)$entries[0]['givenname'][0];
                }
                return $result;
                
            } finally {
                if ($ldapConn) {
                    ldap_unbind($ldapConn);
                }
            }
        }
    }