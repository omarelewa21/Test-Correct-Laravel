<?php
return $settings = array(
    'strict'              => false,
    'debug'               => false,
    // Vul hier de base URL van de applicatie in (voorbeeld: https://example.com)
    'baseurl'             => 'http://test-correct.test',
    // Informatie over de te implementeren Service Provider applicatie
    'sp'                  => array(
        // Het entityID is de unieke idenitfier van de applicatie (voorbeeld: https://example.com/projectnaam)
        'entityId'                 => 'http://test-correct.test/saml2/entree',
        // Informatie over het endpoint waar Entree Federatie de responses naar toestuurt
        'assertionConsumerService' => array(
            // De URL van het endpoint (voorbeeld: https://example.com/index.php?acs)
            'url'     => 'http://test-correct.test/saml2/entree/acs',
            // SAML protocol binding dat gebruikt wordt om de response te versturen
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        // Format van de identifier van het onderwerp van authenticatie
        // Voor Entree Federatie is het vereist dat dit 'unspecified' is
        'NameIDFormat'             => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        // Informatie van het certificaat dat gegenereerd is in stap 2
        'x509cert'                 => 'sp.crt',
        'privateKey'               => 'sp.key'
    ),
    // Informatie over de Entree Federatie applicatie
    // De informatie staat in de metadata van Entree Federatie
    // Metadata staging omgeving: https://hub-s.entree.kennisnet.nl/openaselect/profiles/saml/
    // Metadata productie omgeving: https://hub.entree.kennisnet.nl/openaselect/profiles/saml/
    'idp'                 => array(
        // De unieke identifier van Entree Federatie
//        'entityId'            => 'https://aselect.entree.kennisnet.nl/',
        'entityId'            => 'https://aselect-s.entree.kennisnet.nl/',
        // Endpoint van Entree Federatie waar de authenticatie requests naar toegestuurd worden
        'singleSignOnService' => array(
            // De URL van het endpoint
//            'url'     => 'https://aselect.entree.kennisnet.nl/openaselect/profiles/saml/sso/web',
            'url'     => 'https://aselect-s.entree.kennisnet.nl/openaselect/profiles/saml/sso/web',
            // SAML protocol binding dat gebruikt wordt om de requests naar Entree Federatie te versturen
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        // De public key in de metadata van Entree Federatie, te vinden in de metadata onder het onderdeel IDPSSODescriptor / X509Certificate.
        'x509cert'            => 'MIIF4TCCA8mgAwIBAgIEXXr4LzANBgkqhkiG9w0BAQsFADCBoDELMAkGA1UEBhMCTkwxFTATBgNV
BAgTDFp1aWQtSG9sbGFuZDETMBEGA1UEBxMKWm9ldGVybWVlcjEcMBoGA1UEChMTU3RpY2h0aW5n
IEtlbm5pc25ldDEZMBcGA1UECxMQRW50cmVlIEZlZGVyYXRpZTEsMCoGA1UEAxMjYXNlbGVjdC5z
dGFnaW5nLmVudHJlZS5rZW5uaXNuZXQubmwwHhcNMTcwNzI0MTMzMTEwWhcNMjIwOTAxMTMzMTEw
WjCBoDELMAkGA1UEBhMCTkwxFTATBgNVBAgTDFp1aWQtSG9sbGFuZDETMBEGA1UEBxMKWm9ldGVy
bWVlcjEcMBoGA1UEChMTU3RpY2h0aW5nIEtlbm5pc25ldDEZMBcGA1UECxMQRW50cmVlIEZlZGVy
YXRpZTEsMCoGA1UEAxMjYXNlbGVjdC5zdGFnaW5nLmVudHJlZS5rZW5uaXNuZXQubmwwggIiMA0G
CSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQCzk5qEmbwFvrVVDApB2ggPoYQQiIXNiW+1J4fId9p5
51vGgXBjVQvoYPcYpLHhHdh+LaxMWpCES67NUIXZODZtqfj6Q3iQkqE6+N75RSRg4sPG/FmsYCnI
TBNfQyVIyNfo/5WvGbCtKZarXMtYk6hthRuwDcxOf4xAhALyS3x6cRmedSpZVBg8Ysgdriivf4AQ
lNy7yHxhN+vRWJ4F5D1ZFg5JKNl5o4uh2QVLymIH4MJAe5KMfsEQao6HTQgWRLWHcmfTCMDlYyZv
149iCwPrN7rl8tI1BBx9gnf6Yrbc4VWGFn+rnEYub4CcuLSgc0f6uUrL/AhJwy+2PaHXAcnrpQ7g
71BOG1hbHgpdnr+dRsjzHFUf4axBsVqxr6scTXfcYDzMK2HyGheGjYpa8uX+b1s9V+0KMWbBIp7X
TmJDoTnrHj7K+pXAa1ZUK3/kLuexRYIDf6MUh6wTn4AT8v/pERdg5HdIcV8pg/cilxkSu7iwXGgn
ZpGzJYmfu0NcZLbGea1fEpAAB+0yHw1M3mx6FSKPE27DkHK592B0h1GzX6OM0ngURMl7gK+jVwGH
m6JJrKaSqSOXRy79a3HY7W8+WLMOlssq4aJT5nhxWGBDy+aSy0W+frhKV3WeXevYqG/gRiv8WVV+
m2D29c49cMzr18Z5+gkAuCJg7Sha6AzyYwIDAQABoyEwHzAdBgNVHQ4EFgQUaE7jTfVApSIl5iNX
6FsEFgSLBC4wDQYJKoZIhvcNAQELBQADggIBAKgaSgLmtFYFU3sNPrRJSAd+xqb1TE7GJGC1ywNd
SicZiV5MVkFYzm7UkUArbuBnFspkMaxYk4pgXvJQrGppEJAhGfpOwsSNZcn35hRfvvGpbiIPw4PN
c6//g5qbWXgVqkzzNZ3Wu6HbG5zBznO1kFu1izXIAIJC534EGbkAvcrn5axSOZt4eR31lSsgckNk
QLV568kyMi3i/dvwC5FL12dbCASqNt8+RvUynhC0BpVJ+ihppPMEYxFcoLorh5uXNpPv3CiUc55g
msjF57VYmicHghikNQ31WAuKntanyxExLOwSumM7MJR411OF+V+NmpO7x+Wixzxv0tPRh5Wyo6vG
puQWTMiGz2idGIfaxiJ8JXa9ubUfOpjrXsgtkYlu1R3K2Cbt9n7V2UaVNyGQMT2m6WCupPXaP/UE
DwwVN+YKl7O/tLUYRvmTnOed5zpOwX6WELT9Gshmi9T3lVn/p3XnGxxz8RpnrcQbc/MvGjybsRsj
6uFjsGWLBSqhn9e10awAl9JrJtojDje7PhADopUpe9dGbKBBgUBewDorkf55L+l5XNiH3f+Ne7jn
7uD696761sUpsGnDlWjf6oGIsG8YulDhAf8hZTOlB4Xi3GowtQ42gCKVgE1cgXeDRjkOIgSHhXuF
N99D5dVbx2vmPcidF8Lqre2S6R7AvpP0vVuh'
    ),

    // Alle verzonden en ontvangen berichten moeten gesigned zijn
    'authnRequestsSigned' => true,

    // Algorithm that the toolkit will use on signing process. Options:
    // Entree Federatie only uses SHA1 for SAML signing.
    'signatureAlgorithm'  => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',

    // Contact informatie
    'contactPerson'       => array(
        'technical' => array(
            'givenName'    => 'martin folkerts',
            'emailAddress' => 'martin@sobit.nl'
        ),
        'support'   => array(
            'givenName'    => 'Robert',
            'emailAddress' => 'support@test-correct.nl'
        ),
    ),
    // Organisatie informatie
    'organization'        => array(
        'nl-nl' => array(
            'name'        => 'Teach and Learn company',
            'displayname' => 'TLC',
            'url'         => 'https://www.test-correct.nl/'
        ),
    ),

);
