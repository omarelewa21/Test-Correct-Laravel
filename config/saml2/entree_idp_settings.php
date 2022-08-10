<?php

if(env('ENTREE_IDP_STATE','') == 'production'){
    return $settings = array(
        'strict'   => false,
        'debug'    => false,
        // Vul hier de base URL van de applicatie in (voorbeeld: https://example.com)
        'baseurl'  => 'https://welcome.test-correct.nl',
        // Informatie over de te implementeren Service Provider applicatie
        'sp'       => array(
            // Het entityID is de unieke idenitfier van de applicatie (voorbeeld: https://example.com/projectnaam)
            'entityId'                 => 'https://welcome.test-correct.nl/saml2/entree',
            // Informatie over het endpoint waar Entree Federatie de responses naar toestuurt
            'assertionConsumerService' => array(
                // De URL van het endpoint (voorbeeld: https://example.com/index.php?acs)
                'url'     => 'https://welcome.test-correct.nl/saml2/entree/acs',
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
            'entityId'            => 'https://aselect.entree.kennisnet.nl/',
//        'entityId'            => 'https://aselect-s.entree.kennisnet.nl/',
            // Endpoint van Entree Federatie waar de authenticatie requests naar toegestuurd worden
            'singleSignOnService' => array(
                // De URL van het endpoint
                'url'     => 'https://aselect.entree.kennisnet.nl/openaselect/profiles/saml/sso/web',
//            'url'     => 'https://aselect-s.entree.kennisnet.nl/openaselect/profiles/saml/sso/web',
                // new setup uses small and full set of attributes. We always start with the small set
                // and do a rerun with the full set if the school location has sso active
                'url_smallset' => 'a',
                'url_fullset' => 'b',
                // SAML protocol binding dat gebruikt wordt om de requests naar Entree Federatie te versturen
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            // De public key in de metadata van Entree Federatie, te vinden in de metadata onder het onderdeel IDPSSODescriptor / X509Certificate.
//        'x509cert'            => 'MIIF4TCCA8mgAwIBAgIEXXr4LzANBgkqhkiG9w0BAQsFADCBoDELMAkGA1UEBhMCTkwxFTATBgNV
//BAgTDFp1aWQtSG9sbGFuZDETMBEGA1UEBxMKWm9ldGVybWVlcjEcMBoGA1UEChMTU3RpY2h0aW5n
//IEtlbm5pc25ldDEZMBcGA1UECxMQRW50cmVlIEZlZGVyYXRpZTEsMCoGA1UEAxMjYXNlbGVjdC5z
//dGFnaW5nLmVudHJlZS5rZW5uaXNuZXQubmwwHhcNMTcwNzI0MTMzMTEwWhcNMjIwOTAxMTMzMTEw
//WjCBoDELMAkGA1UEBhMCTkwxFTATBgNVBAgTDFp1aWQtSG9sbGFuZDETMBEGA1UEBxMKWm9ldGVy
//bWVlcjEcMBoGA1UEChMTU3RpY2h0aW5nIEtlbm5pc25ldDEZMBcGA1UECxMQRW50cmVlIEZlZGVy
//YXRpZTEsMCoGA1UEAxMjYXNlbGVjdC5zdGFnaW5nLmVudHJlZS5rZW5uaXNuZXQubmwwggIiMA0G
//CSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQCzk5qEmbwFvrVVDApB2ggPoYQQiIXNiW+1J4fId9p5
//51vGgXBjVQvoYPcYpLHhHdh+LaxMWpCES67NUIXZODZtqfj6Q3iQkqE6+N75RSRg4sPG/FmsYCnI
//TBNfQyVIyNfo/5WvGbCtKZarXMtYk6hthRuwDcxOf4xAhALyS3x6cRmedSpZVBg8Ysgdriivf4AQ
//lNy7yHxhN+vRWJ4F5D1ZFg5JKNl5o4uh2QVLymIH4MJAe5KMfsEQao6HTQgWRLWHcmfTCMDlYyZv
//149iCwPrN7rl8tI1BBx9gnf6Yrbc4VWGFn+rnEYub4CcuLSgc0f6uUrL/AhJwy+2PaHXAcnrpQ7g
//71BOG1hbHgpdnr+dRsjzHFUf4axBsVqxr6scTXfcYDzMK2HyGheGjYpa8uX+b1s9V+0KMWbBIp7X
//TmJDoTnrHj7K+pXAa1ZUK3/kLuexRYIDf6MUh6wTn4AT8v/pERdg5HdIcV8pg/cilxkSu7iwXGgn
//ZpGzJYmfu0NcZLbGea1fEpAAB+0yHw1M3mx6FSKPE27DkHK592B0h1GzX6OM0ngURMl7gK+jVwGH
//m6JJrKaSqSOXRy79a3HY7W8+WLMOlssq4aJT5nhxWGBDy+aSy0W+frhKV3WeXevYqG/gRiv8WVV+
//m2D29c49cMzr18Z5+gkAuCJg7Sha6AzyYwIDAQABoyEwHzAdBgNVHQ4EFgQUaE7jTfVApSIl5iNX
//6FsEFgSLBC4wDQYJKoZIhvcNAQELBQADggIBAKgaSgLmtFYFU3sNPrRJSAd+xqb1TE7GJGC1ywNd
//SicZiV5MVkFYzm7UkUArbuBnFspkMaxYk4pgXvJQrGppEJAhGfpOwsSNZcn35hRfvvGpbiIPw4PN
//c6//g5qbWXgVqkzzNZ3Wu6HbG5zBznO1kFu1izXIAIJC534EGbkAvcrn5axSOZt4eR31lSsgckNk
//QLV568kyMi3i/dvwC5FL12dbCASqNt8+RvUynhC0BpVJ+ihppPMEYxFcoLorh5uXNpPv3CiUc55g
//msjF57VYmicHghikNQ31WAuKntanyxExLOwSumM7MJR411OF+V+NmpO7x+Wixzxv0tPRh5Wyo6vG
//puQWTMiGz2idGIfaxiJ8JXa9ubUfOpjrXsgtkYlu1R3K2Cbt9n7V2UaVNyGQMT2m6WCupPXaP/UE
//DwwVN+YKl7O/tLUYRvmTnOed5zpOwX6WELT9Gshmi9T3lVn/p3XnGxxz8RpnrcQbc/MvGjybsRsj
//6uFjsGWLBSqhn9e10awAl9JrJtojDje7PhADopUpe9dGbKBBgUBewDorkf55L+l5XNiH3f+Ne7jn
//7uD696761sUpsGnDlWjf6oGIsG8YulDhAf8hZTOlB4Xi3GowtQ42gCKVgE1cgXeDRjkOIgSHhXuF
//N99D5dVbx2vmPcidF8Lqre2S6R7AvpP0vVuh'
//    ),
            // @@ Dit certificaat vervangen voor PRODUCTIE
            // https://hub.entree.kennisnet.nl/openaselect/profiles/saml/
            'x509cert' => 'MIID0TCCArmgAwIBAgIESb+pqDANBgkqhkiG9w0BAQsFADCBmDELMAkGA1UEBhMCTkwxFTATBgNV
BAgTDFp1aWQtSG9sbGFuZDETMBEGA1UEBxMKWm9ldGVybWVlcjEcMBoGA1UEChMTU3RpY2h0aW5n
IEtlbm5pc25ldDEZMBcGA1UECxMQRW50cmVlIEZlZGVyYXRpZTEkMCIGA1UEAxMbYXNlbGVjdC5l
bnRyZWUua2VubmlzbmV0Lm5sMB4XDTIyMDYzMDExMjYzNFoXDTI1MDYyOTExMjYzNFowgZgxCzAJ
BgNVBAYTAk5MMRUwEwYDVQQIEwxadWlkLUhvbGxhbmQxEzARBgNVBAcTClpvZXRlcm1lZXIxHDAa
BgNVBAoTE1N0aWNodGluZyBLZW5uaXNuZXQxGTAXBgNVBAsTEEVudHJlZSBGZWRlcmF0aWUxJDAi
BgNVBAMTG2FzZWxlY3QuZW50cmVlLmtlbm5pc25ldC5ubDCCASIwDQYJKoZIhvcNAQEBBQADggEP
ADCCAQoCggEBAKdM6toMzJ2hFrem7rL3iX8EO40IJzDdEABcPqK+Ef/OAaHm2bkJid5tqOnBIOoD
J7xh71m7xmDsr49CqsDGFoxdOfO+WmWDpRSq72vMGJfp7wSUK6GHGtfAV/90N5UWY/GrvBrTO9v+
bxHt8szmFwMk3NWJ60CkCwgNDBaMcx17OBLEp4A5tlvDzjdnAoKIvLruTazOMoN4oXWV+DJPmXlf
OLE0DqZ6pEWLEDq/Z6fdyK0X9+z8+FfR053tB2cP2e5Fs6cwRl2FLlHD7WofgX13egXTXK2Bj0XR
CxG0QOEcVgRW8QiGISsHbn/0ab9s+4hGTgjruvSBYGaj/jhZvR8CAwEAAaMhMB8wHQYDVR0OBBYE
FFoX0DmWDccLiC6UBFGZpfoXs4ykMA0GCSqGSIb3DQEBCwUAA4IBAQBl1oF8xMC6S8Wk+udz+FG2
n9k9W6yNIXn1LwvKnqwAWGMMCeRUbCIVZbb5Qn12ZvMzHrcYBzA5Hm6SIzRwAsHxuWvmSnJ+1gIo
jW2nSLIizvyvKiW9VrutUBtGBlS7rqGLUs/l1YRq7/7NnyaE/IXehPM2hqRkbhp4lfQccDbBJE01
0sA/AFVkG7CirHXUG21q2XaZ+UwVWjVqEertYbTQMjZdcODqiSgH8J85QB5b4zt5ylidkkcY0ZiG
y2SJeyusQWRDyzlVETToOARiTXRCsisMrK4L1UBeCADfEVzxnHQpfKw6nE1BE8yi4WbyXFDNRnhC
1Ug6uRVGIB1MvTj2'),
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
} else {
    return $settings = array(
        'strict'   => false,
        'debug'    => false,
        // Vul hier de base URL van de applicatie in (voorbeeld: https://example.com)
        'baseurl'  => env('BASE_URL'),
        // Informatie over de te implementeren Service Provider applicatie
        'sp'       => array(
            // Het entityID is de unieke idenitfier van de applicatie (voorbeeld: https://example.com/projectnaam)
            'entityId'                 => env('BASE_URL').'saml2/entree',
            // Informatie over het endpoint waar Entree Federatie de responses naar toestuurt
            'assertionConsumerService' => array(
                // De URL van het endpoint (voorbeeld: https://example.com/index.php?acs)
                'url'     => env('BASE_URL').'saml2/entree/acs',
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
//            'entityId'            => 'https://aselect.entree.kennisnet.nl/',
            'entityId'            => config('entree.use_with_2_urls') ? 'https://engine.entree-s.kennisnet.nl/authentication/idp/metadata' : 'https://aselect-s.entree.kennisnet.nl/',
            // Endpoint van Entree Federatie waar de authenticatie requests naar toegestuurd worden
            'singleSignOnService' => array(
                // De URL van het endpoint
//                'url'     => 'https://aselect.entree.kennisnet.nl/openaselect/profiles/saml/sso/web',
                'url'     => config('entree.use_with_2_urls') ? 'https://engine.entree-s.kennisnet.nl/authentication/idp/single-sign-on' : 'https://aselect-s.entree.kennisnet.nl/openaselect/profiles/saml/sso/web',
                // SAML protocol binding dat gebruikt wordt om de requests naar Entree Federatie te versturen
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            // De public key in de metadata van Entree Federatie, te vinden in de metadata onder het onderdeel IDPSSODescriptor / X509Certificate.
            // @@ Dit certificaat vervangen voor TESTING
            // https://hub-s.entree.kennisnet.nl/openaselect/profiles/saml/
            'x509cert'            => config('entree.use_with_2_urls') ? 'MIID3TCCAsWgAwIBAgIUdzuUMExjcJhUNfPwjPIBOmU6zKIwDQYJKoZIhvcNAQELBQAwfjELMAkGA1UEBhMCTkwxFTATBgNVBAgMDFp1aWQtSG9sbGFuZDETMBEGA1UEBwwKWm9ldGVybWVlcjEcMBoGA1UECgwTU3RpY2h0aW5nIEtlbm5pc25ldDElMCMGA1UEAwwcZW5naW5lLmVudHJlZS1zLmtlbm5pc25ldC5ubDAeFw0yMjAyMjMxNTU1MThaFw0zMjAyMjExNTU1MThaMH4xCzAJBgNVBAYTAk5MMRUwEwYDVQQIDAxadWlkLUhvbGxhbmQxEzARBgNVBAcMClpvZXRlcm1lZXIxHDAaBgNVBAoME1N0aWNodGluZyBLZW5uaXNuZXQxJTAjBgNVBAMMHGVuZ2luZS5lbnRyZWUtcy5rZW5uaXNuZXQubmwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCkzrbtoUvD0yBA6dFUlJauTBPkFNa492H8bQgqCQwNq+O4sZyJxQDqHlgTqBeCHFoI6q9HyiyauQ4Cskn16SI7nKmVdAnnzbPbv0+bKGg1RBsyx4gUuxiH6W7X0xfkje++eKyCEdTHw8v6RtKOflboDYNgJUkBreqAdsqb148WEaN8hqe6II6W60hJmeviFYoh69Pqx3qNiZ5shdFGxdITekyAfdUkdvyJKuiVaLAH2x8T2RK/OWGaTtyZOelaC6UkaTxAkHwkiCdAqc7y8QtcgBQVGpMYEYjunA8f9RUvmaOW8UhWgNRKzqa+jvD4N8PmxXIvKQxSqnmDf2Ih3lqJAgMBAAGjUzBRMB0GA1UdDgQWBBTY22MincDheeAjZ05vwPU8uTYiHDAfBgNVHSMEGDAWgBTY22MincDheeAjZ05vwPU8uTYiHDAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQAhQwKVSTOYOSz4qXsSvHiYuiDwNIeM3zQEmD6WQy2bdANXuI59WyUvC1t83+yZihakaCd3ayQRU7RiNBu/Sa+sPS3CK/pO4GvQEi6BCpsuk4siiHhZcQthquNKywlEzRgtDQX7vEm6Kt3dySYrn14Tx39aPjKOr37o/HQ28necub4xnRwjWFfHblNuUHz2jr6p0GW20THA0utGrYM3cR3rKEsebvLfq9e31Wzd1lLKu08CgKPBDO9VZ1SvAuRJ10IUgyYp5vejZCcSYqlrS00FJoz6jbbTjxBPB0JesgtwS54xTsh7yfMP0uae0R9azjSkvnuIsW5pda7B8cH7zz5R' : 'MIIF4TCCA8mgAwIBAgIEXXr4LzANBgkqhkiG9w0BAQsFADCBoDELMAkGA1UEBhMCTkwxFTATBgNV
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
}


