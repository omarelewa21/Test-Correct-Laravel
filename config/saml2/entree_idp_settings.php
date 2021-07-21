<?php
return $settings = array(
    'strict'   => false,
    'debug'    => false,
    // Vul hier de base URL van de applicatie in (voorbeeld: https://example.com)
    'baseurl'  => env('ENTREE_IDP_SP_BASE_URL',''),
    // Informatie over de te implementeren Service Provider applicatie
    'sp'       => array(
        // Het entityID is de unieke idenitfier van de applicatie (voorbeeld: https://example.com/projectnaam)
        'entityId'                 => env('ENTREE_IDP_SP_ENTITYID',''),
        // Informatie over het endpoint waar Entree Federatie de responses naar toestuurt
        'assertionConsumerService' => array(
            // De URL van het endpoint (voorbeeld: https://example.com/index.php?acs)
            'url'     => env('ENTREE_IDP_SP_ASSERTIONCONSUMERSERVICE_URL',''),
            // SAML protocol binding dat gebruikt wordt om de response te versturen
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        // Format van de identifier van het onderwerp van authenticatie
        // Voor Entree Federatie is het vereist dat dit 'unspecified' is
        'NameIDFormat'             => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        // Informatie van het certificaat dat gegenereerd is in stap 2
        'x509cert'                 => 'MIID7zCCAtegAwIBAgIUDYroqRdJJTylt+NC3Q1N25blfIcwDQYJKoZIhvcNAQEL
BQAwgYYxCzAJBgNVBAYTAm5sMRMwEQYDVQQIDApHZWxkZXJsYW5kMRMwEQYDVQQH
DApXYWdlbmluZ2VuMQ4wDAYDVQQKDAVTb2JpdDELMAkGA1UECwwCYnYxETAPBgNV
BAMMCHNvYml0Lm5sMR0wGwYJKoZIhvcNAQkBFg5hZG1pbkBzb2JpdC5ubDAeFw0y
MTA1MTExMzM2MDBaFw0zMTA1MTExMzM2MDBaMIGGMQswCQYDVQQGEwJubDETMBEG
A1UECAwKR2VsZGVybGFuZDETMBEGA1UEBwwKV2FnZW5pbmdlbjEOMAwGA1UECgwF
U29iaXQxCzAJBgNVBAsMAmJ2MREwDwYDVQQDDAhzb2JpdC5ubDEdMBsGCSqGSIb3
DQEJARYOYWRtaW5Ac29iaXQubmwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEK
AoIBAQC/lYjSLbgwJPw0CoXCc6qgmKsY3s0QXs9i5v8t+NUX1Ow2jFLw9m7Yz0Kq
nJzoXUshD98K2kxSoUkMHqI22gfP4aRStYeZYQ27mz0l0tUhhgRo7EzHp8oEfSuW
rx98yCT9FJjVdNUBGrppnzWadSgi5zRJb1vHq9+70AiDkbxsuL7YrW36y+nwIN7Y
7TtSFNpHMKyJjFbde4R3E5EB/+C27rcdENLnJtr6R6jWlY+Dnu8+WXoqCtcWMuMw
Nt01oeKM2wpsy+uCMH4izwJMr8TC9VGXhOfBf6gG8c72xX+efBW6QfeqKOrppyWg
9bXyHCnUHc0TR09Ml+dYBD66tpjXAgMBAAGjUzBRMB0GA1UdDgQWBBRFmBAPtQKQ
/p+h9GJyHgYHx408GzAfBgNVHSMEGDAWgBRFmBAPtQKQ/p+h9GJyHgYHx408GzAP
BgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQCAtOPj0pxNi4ca9X92
XUQKTnzcbfA9L5gn3MAxK4qfMwrGJOp0cvo42oIV3wr9aU8/HFCiR6z420SQFpsk
8vMZob1jvzih0k65n9LOzEUTkuFe370DN9jT6wdsh106w1yHkAcOM0Xz9NC1l9Sd
FMU2MftIEGbRG5jDDTL5CI6vuBPo9zBkxRXLxR+KZng9RTx9iliO6tlVghuGGgRT
t2waCuhRX+COEsWkWVAerbRH7QnP3YAPdKnB9dkZ0bzM20coxfeHq8F++pqTzgCm
bml7Fk7sIkhXKazpnXQM+fjoeUpQKXaJHEi9nFI9iVPdhcAQhmT0FRhScpi8VH5f
JFGq',
        'privateKey'               => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC/lYjSLbgwJPw0
CoXCc6qgmKsY3s0QXs9i5v8t+NUX1Ow2jFLw9m7Yz0KqnJzoXUshD98K2kxSoUkM
HqI22gfP4aRStYeZYQ27mz0l0tUhhgRo7EzHp8oEfSuWrx98yCT9FJjVdNUBGrpp
nzWadSgi5zRJb1vHq9+70AiDkbxsuL7YrW36y+nwIN7Y7TtSFNpHMKyJjFbde4R3
E5EB/+C27rcdENLnJtr6R6jWlY+Dnu8+WXoqCtcWMuMwNt01oeKM2wpsy+uCMH4i
zwJMr8TC9VGXhOfBf6gG8c72xX+efBW6QfeqKOrppyWg9bXyHCnUHc0TR09Ml+dY
BD66tpjXAgMBAAECggEBAKgCug8f2xQVizsUM0NY7jySRhG0af8+Nf7U9tnZCv8w
UcpwetgoNQNbl2pJl+zKy/T9lytMT3xzRr0jLDyQLiOnDNUfrv/aNhHdTN2brB8P
CVT+TUMWKTqQjqZBg4qJdq6e3nzrTVT/nJS982M19PHO8nKn/sP3PWjWQnEnYCey
wjLKL4ZFTSQHF+FK8UkIJgIWExH2NoOsRS6NS/+ir5+fGMEX/qcSd7gYl6eX5wsU
zQjxPd543mOJhHg7/3e7jb9DUdFLy/jpPyOzpkY0G+9WDUwFepMo4HSKOHXsaS+l
Am5x0VvCkFp6A/Inwsy5NAeiQIPf2EocXge92WmIIoECgYEA844vmVfDjRhnYwfr
5qiSSrve01AUACwXdN/V1RbpkZcZvJT9mKU4pTP48gauX3X5MGrIW+j84+gpge3D
wZOHOtGpfhx2DLEDjZzbUdjf4muISfB0gLjwItz7aoqNzxMXu2TCWAkXFv1hntA/
165UrwaQkpI9zuoGD+5yoNVBapcCgYEAyV+KaZpvzDvFUIl9AVmEs/qj7lixdmT6
mRBGV3mUm86/bDrzW8H9d4M7163n2BcuJdrQDsttazdNS0iINq0fto5LVDtbiPZh
jHQ2JaLH02U9FxvLJjwBYJscdVtxfyDuAvhvOCiRmRGMNaBqBEjCUqBN0kczikYR
gSjUwDONS8ECgYAtSXD8WF9aKwF+Xoi0uP+Kuegy7p7pcUljSAOgvcPseGYmtKV6
7q3buhA+IJPn2C3fnNtoi6gKUK73I9jUc7QfccDMXEpvDbMVb+cwDt2CYnBTH0zq
anjsYp61LtIzgN9WzuN8LySF99NhDmPwnM/OQ7A6MMshYE6EQ9g4o66oPQKBgCu6
Il0yGuq3Y+5MHKfpX2aRm31LJyX1YXFRVmTyUrHOoESJPIUFR9vm1FzON5T126B+
tkUwKU9pz8/0LRfqWgOTPIpK5WKFVcNhDMz5Xvjpd+2HrIJd71Kh+/kD5U5cwTJF
7ii8rnkVlWOjtMG/Zur4Qk3SBkAVXQG18xTVXO5BAoGBAKo1hOcwPpMH5C1TXsyM
eFkZNn7cra8Cdj5AxE8wV1IhHILv9h2ZvdsSY6d6aB9NOK1ceFFPI3rKz6YDKELo
rbqiwrpAOJkiB3CQ+3B7KmtNLowpaZCcNOsCUWX7oslPnZx1G+qYVMxjTbkIcx6c
jzwnvGSTRr4zLbXwz+RZmkre'
    ),
    // Informatie over de Entree Federatie applicatie
    // De informatie staat in de metadata van Entree Federatie
    // Metadata staging omgeving: https://hub-s.entree.kennisnet.nl/openaselect/profiles/saml/
    // Metadata productie omgeving: https://hub.entree.kennisnet.nl/openaselect/profiles/saml/
    'idp'      => array(
        // De unieke identifier van Entree Federatie
        'entityId'            => env('ENTREE_IDP_IDP_ENTITYID',''),
        // Endpoint van Entree Federatie waar de authenticatie requests naar toegestuurd worden
        'singleSignOnService' => array(
            // De URL van het endpoint
            'url'     => env('ENTREE_IDP_IDP_SINGLESIGNONSERVICE_URL',''),
            // SAML protocol binding dat gebruikt wordt om de requests naar Entree Federatie te versturen
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        // De public key in de metadata van Entree Federatie, te vinden in de metadata onder het onderdeel IDPSSODescriptor / X509Certificate.
        'x509cert'            => env('ENTREE_IDP_DP_X509CERT',''),
    ),
    'security' => array(
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
    )

);
