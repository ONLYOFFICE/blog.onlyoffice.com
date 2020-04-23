appAddThisWordPress.controller('RegistrationCtrl', function(
  $scope,
  $q,
  $wordpress,
  $filter,
  $stateParams,
  $state,
  $darkseid
) {
  $scope.globalOptions = {};
  $scope.registrationFormModel = {};
  $scope.successfulNewRegistration = false;
  $scope.loadingStatus = true;
  $scope.loadingMessage = 'progress_message_loading';

  $scope.templateBaseUrl = $wordpress.templateBaseUrl();
  jQuery('[id="wpcontent"]').attr('class', 'registration-page');

  // Todo: move this to i10n
  $scope.countryOptions =[
    {
      value: 'US',
      display: 'United States',
      info: 'United States'
    },
    {
      display: '──────────',
      disabled: true
    },
    {
      value: 'AF',
      display: 'Afghanistan',
      info: 'Afghanistan'
    },
    {
      value: 'AX',
      display: 'Aland Islands',
      info: 'Aland Islands'
    },
    {
      value: 'AL',
      display: 'Albania',
      info: 'Albania'
    },
    {
      value: 'DZ',
      display: 'Algeria',
      info: 'Algeria'
    },
    {
      value: 'AS',
      display: 'American Samoa',
      info: 'American Samoa'
    },
    {
      value: 'AD',
      display: 'Andorra',
      info: 'Andorra'
    },
    {
      value: 'AO',
      display: 'Angola',
      info: 'Angola'
    },
    {
      value: 'AI',
      display: 'Anguilla',
      info: 'Anguilla'
    },
    {
      value: 'AQ',
      display: 'Antarctica',
      info: 'Antarctica'
    },
    {
      value: 'AG',
      display: 'Antigua and Barbuda',
      info: 'Antigua and Barbuda'
    },
    {
      value: 'AR',
      display: 'Argentina',
      info: 'Argentina'
    },
    {
      value: 'AM',
      display: 'Armenia',
      info: 'Armenia'
    },
    {
      value: 'AW',
      display: 'Aruba',
      info: 'Aruba'
    },
    {
      value: 'AU',
      display: 'Australia',
      info: 'Australia'
    },
    {
      value: 'AT',
      display: 'Austria',
      info: 'Austria'
    },
    {
      value: 'AZ',
      display: 'Azerbaijan',
      info: 'Azerbaijan'
    },
    {
      value: 'BS',
      display: 'Bahamas',
      info: 'Bahamas'
    },
    {
      value: 'BH',
      display: 'Bahrain',
      info: 'Bahrain'
    },
    {
      value: 'BD',
      display: 'Bangladesh',
      info: 'Bangladesh'
    },
    {
      value: 'BB',
      display: 'Barbados',
      info: 'Barbados'
    },
    {
      value: 'BY',
      display: 'Belarus',
      info: 'Belarus'
    },
    {
      value: 'BE',
      display: 'Belgium',
      info: 'Belgium'
    },
    {
      value: 'BZ',
      display: 'Belize',
      info: 'Belize'
    },
    {
      value: 'BJ',
      display: 'Benin',
      info: 'Benin'
    },
    {
      value: 'BM',
      display: 'Bermuda',
      info: 'Bermuda'
    },
    {
      value: 'BT',
      display: 'Bhutan',
      info: 'Bhutan'
    },
    {
      value: 'BO',
      display: 'Bolivia',
      info: 'Bolivia'
    },
    {
      value: 'BA',
      display: 'Bosnia and Herzegovina',
      info: 'Bosnia and Herzegovina'
    },
    {
      value: 'BW',
      display: 'Botswana',
      info: 'Botswana'
    },
    {
      value: 'BV',
      display: 'Bouvet Island',
      info: 'Bouvet Island'
    },
    {
      value: 'BR',
      display: 'Brazil',
      info: 'Brazil'
    },
    {
      value: 'IO',
      display: 'British Indian Ocean Territory',
      info: 'British Indian Ocean Territory'
    },
    {
      value: 'BN',
      display: 'Brunei Darussalam',
      info: 'Brunei Darussalam'
    },
    {
      value: 'BG',
      display: 'Bulgaria',
      info: 'Bulgaria'
    },
    {
      value: 'BF',
      display: 'Burkina Faso',
      info: 'Burkina Faso'
    },
    {
      value: 'BI',
      display: 'Burundi',
      info: 'Burundi'
    },
    {
      value: 'KH',
      display: 'Cambodia',
      info: 'Cambodia'
    },
    {
      value: 'CM',
      display: 'Cameroon',
      info: 'Cameroon'
    },
    {
      value: 'CA',
      display: 'Canada',
      info: 'Canada'
    },
    {
      value: 'CV',
      display: 'Cape Verde',
      info: 'Cape Verde'
    },
    {
      value: 'KY',
      display: 'Cayman Islands',
      info: 'Cayman Islands'
    },
    {
      value: 'CF',
      display: 'Central African Republic',
      info: 'Central African Republic'
    },
    {
      value: 'TD',
      display: 'Chad',
      info: 'Chad'
    },
    {
      value: 'CL',
      display: 'Chile',
      info: 'Chile'
    },
    {
      value: 'CN',
      display: 'China',
      info: 'China'
    },
    {
      value: 'CX',
      display: 'Christmas Island',
      info: 'Christmas Island'
    },
    {
      value: 'CC',
      display: 'Cocos (Keeling) Islands',
      info: 'Cocos (Keeling) Islands'
    },
    {
      value: 'CO',
      display: 'Colombia',
      info: 'Colombia'
    },
    {
      value: 'KM',
      display: 'Comoros',
      info: 'Comoros'
    },
    {
      value: 'CG',
      display: 'Congo',
      info: 'Congo'
    },
    {
      value: 'CD',
      display: 'Congo, The Democratic Republic of the',
      info: 'Congo, The Democratic Republic of the'
    },
    {
      value: 'CK',
      display: 'Cook Islands',
      info: 'Cook Islands'
    },
    {
      value: 'CR',
      display: 'Costa Rica',
      info: 'Costa Rica'
    },
    {
      value: 'CI',
      display: 'Cote D\'Ivoire',
      info: 'Cote D\'Ivoire'
    },
    {
      value: 'HR',
      display: 'Croatia',
      info: 'Croatia'
    },
    {
      value: 'CY',
      display: 'Cyprus',
      info: 'Cyprus'
    },
    {
      value: 'CZ',
      display: 'Czech Republic',
      info: 'Czech Republic'
    },
    {
      value: 'DK',
      display: 'Denmark',
      info: 'Denmark'
    },
    {
      value: 'DJ',
      display: 'Djibouti',
      info: 'Djibouti'
    },
    {
      value: 'DM',
      display: 'Dominica',
      info: 'Dominica'
    },
    {
      value: 'DO',
      display: 'Dominican Republic',
      info: 'Dominican Republic'
    },
    {
      value: 'TP',
      display: 'East Timor',
      info: 'East Timor'
    },
    {
      value: 'EC',
      display: 'Ecuador',
      info: 'Ecuador'
    },
    {
      value: 'EG',
      display: 'Egypt',
      info: 'Egypt'
    },
    {
      value: 'SV',
      display: 'El Salvador',
      info: 'El Salvador'
    },
    {
      value: 'GQ',
      display: 'Equatorial Guinea',
      info: 'Equatorial Guinea'
    },
    {
      value: 'ER',
      display: 'Eritrea',
      info: 'Eritrea'
    },
    {
      value: 'EE',
      display: 'Estonia',
      info: 'Estonia'
    },
    {
      value: 'ET',
      display: 'Ethiopia',
      info: 'Ethiopia'
    },
    {
      value: 'FO',
      display: 'Faeroe Islands',
      info: 'Faeroe Islands'
    },
    {
      value: 'FK',
      display: 'Falkland Islands (Malvinas)',
      info: 'Falkland Islands (Malvinas)'
    },
    {
      value: 'FJ',
      display: 'Fiji',
      info: 'Fiji'
    },
    {
      value: 'FI',
      display: 'Finland',
      info: 'Finland'
    },
    {
      value: 'FR',
      display: 'France',
      info: 'France'
    },
    {
      value: 'FX',
      display: 'France, Metropolitan',
      info: 'France, Metropolitan'
    },
    {
      value: 'GF',
      display: 'French Guiana',
      info: 'French Guiana'
    },
    {
      value: 'PF',
      display: 'French Polynesia',
      info: 'French Polynesia'
    },
    {
      value: 'TF',
      display: 'French Southern Territories',
      info: 'French Southern Territories'
    },
    {
      value: 'GA',
      display: 'Gabon',
      info: 'Gabon'
    },
    {
      value: 'GM',
      display: 'Gambia',
      info: 'Gambia'
    },
    {
      value: 'GE',
      display: 'Georgia',
      info: 'Georgia'
    },
    {
      value: 'DE',
      display: 'Germany',
      info: 'Germany'
    },
    {
      value: 'GH',
      display: 'Ghana',
      info: 'Ghana'
    },
    {
      value: 'GI',
      display: 'Gibraltar',
      info: 'Gibraltar'
    },
    {
      value: 'GR',
      display: 'Greece',
      info: 'Greece'
    },
    {
      value: 'GL',
      display: 'Greenland',
      info: 'Greenland'
    },
    {
      value: 'GD',
      display: 'Grenada',
      info: 'Grenada'
    },
    {
      value: 'GP',
      display: 'Guadeloupe',
      info: 'Guadeloupe'
    },
    {
      value: 'GU',
      display: 'Guam',
      info: 'Guam'
    },
    {
      value: 'GT',
      display: 'Guatemala',
      info: 'Guatemala'
    },
    {
      value: 'GG',
      display: 'Guernsey',
      info: 'Guernsey'
    },
    {
      value: 'GN',
      display: 'Guinea',
      info: 'Guinea'
    },
    {
      value: 'GW',
      display: 'Guinea-Bissau',
      info: 'Guinea-Bissau'
    },
    {
      value: 'GY',
      display: 'Guyana',
      info: 'Guyana'
    },
    {
      value: 'HT',
      display: 'Haiti',
      info: 'Haiti'
    },
    {
      value: 'HM',
      display: 'Heard and Mc Donald Islands',
      info: 'Heard and Mc Donald Islands'
    },
    {
      value: 'VA',
      display: 'Holy See (Vatican City State)',
      info: 'Holy See (Vatican City State)'
    },
    {
      value: 'HN',
      display: 'Honduras',
      info: 'Honduras'
    },
    {
      value: 'HK',
      display: 'Hong Kong',
      info: 'Hong Kong'
    },
    {
      value: 'HU',
      display: 'Hungary',
      info: 'Hungary'
    },
    {
      value: 'IS',
      display: 'Iceland',
      info: 'Iceland'
    },
    {
      value: 'IN',
      display: 'India',
      info: 'India'
    },
    {
      value: 'ID',
      display: 'Indonesia',
      info: 'Indonesia'
    },
    {
      value: 'IQ',
      display: 'Iraq',
      info: 'Iraq'
    },
    {
      value: 'IE',
      display: 'Ireland',
      info: 'Ireland'
    },
    {
      value: 'IM',
      display: 'Isle of Man',
      info: 'Isle of Man'
    },
    {
      value: 'IL',
      display: 'Israel',
      info: 'Israel'
    },
    {
      value: 'IT',
      display: 'Italy',
      info: 'Italy'
    },
    {
      value: 'JM',
      display: 'Jamaica',
      info: 'Jamaica'
    },
    {
      value: 'JP',
      display: 'Japan',
      info: 'Japan'
    },
    {
      value: 'JE',
      display: 'Jersey',
      info: 'Jersey'
    },
    {
      value: 'JO',
      display: 'Jordan',
      info: 'Jordan'
    },
    {
      value: 'KZ',
      display: 'Kazakhstan',
      info: 'Kazakhstan'
    },
    {
      value: 'KE',
      display: 'Kenya',
      info: 'Kenya'
    },
    {
      value: 'KI',
      display: 'Kiribati',
      info: 'Kiribati'
    },
    {
      value: 'KR',
      display: 'Korea, Republic of',
      info: 'Korea, Republic of'
    },
    {
      value: 'XK',
      display: 'Kosovo',
      info: 'Kosovo'
    },
    {
      value: 'KW',
      display: 'Kuwait',
      info: 'Kuwait'
    },
    {
      value: 'KG',
      display: 'Kyrgyzstan',
      info: 'Kyrgyzstan'
    },
    {
      value: 'LA',
      display: 'Lao People\'s Democratic Republic',
      info: 'Lao People\'s Democratic Republic'
    },
    {
      value: 'LV',
      display: 'Latvia',
      info: 'Latvia'
    },
    {
      value: 'LB',
      display: 'Lebanon',
      info: 'Lebanon'
    },
    {
      value: 'LS',
      display: 'Lesotho',
      info: 'Lesotho'
    },
    {
      value: 'LR',
      display: 'Liberia',
      info: 'Liberia'
    },
    {
      value: 'LY',
      display: 'Libya',
      info: 'Libya'
    },
    {
      value: 'LI',
      display: 'Liechtenstein',
      info: 'Liechtenstein'
    },
    {
      value: 'LT',
      display: 'Lithuania',
      info: 'Lithuania'
    },
    {
      value: 'LU',
      display: 'Luxembourg',
      info: 'Luxembourg'
    },
    {
      value: 'MO',
      display: 'Macau',
      info: 'Macau'
    },
    {
      value: 'MK',
      display: 'Macedonia',
      info: 'Macedonia'
    },
    {
      value: 'MG',
      display: 'Madagascar',
      info: 'Madagascar'
    },
    {
      value: 'MW',
      display: 'Malawi',
      info: 'Malawi'
    },
    {
      value: 'MY',
      display: 'Malaysia',
      info: 'Malaysia'
    },
    {
      value: 'MV',
      display: 'Maldives',
      info: 'Maldives'
    },
    {
      value: 'ML',
      display: 'Mali',
      info: 'Mali'
    },
    {
      value: 'MT',
      display: 'Malta',
      info: 'Malta'
    },
    {
      value: 'MH',
      display: 'Marshall Islands',
      info: 'Marshall Islands'
    },
    {
      value: 'MQ',
      display: 'Martinique',
      info: 'Martinique'
    },
    {
      value: 'MR',
      display: 'Mauritania',
      info: 'Mauritania'
    },
    {
      value: 'MU',
      display: 'Mauritius',
      info: 'Mauritius'
    },
    {
      value: 'YT',
      display: 'Mayotte',
      info: 'Mayotte'
    },
    {
      value: 'MX',
      display: 'Mexico',
      info: 'Mexico'
    },
    {
      value: 'FM',
      display: 'Micronesia, Federated States of',
      info: 'Micronesia, Federated States of'
    },
    {
      value: 'MD',
      display: 'Moldova, Republic of',
      info: 'Moldova, Republic of'
    },
    {
      value: 'MC',
      display: 'Monaco',
      info: 'Monaco'
    },
    {
      value: 'MN',
      display: 'Mongolia',
      info: 'Mongolia'
    },
    {
      value: 'ME',
      display: 'Montenegro',
      info: 'Montenegro'
    },
    {
      value: 'MS',
      display: 'Montserrat',
      info: 'Montserrat'
    },
    {
      value: 'MA',
      display: 'Morocco',
      info: 'Morocco'
    },
    {
      value: 'MZ',
      display: 'Mozambique',
      info: 'Mozambique'
    },
    {
      value: 'MM',
      display: 'Myanmar',
      info: 'Myanmar'
    },
    {
      value: 'NA',
      display: 'Namibia',
      info: 'Namibia'
    },
    {
      value: 'NR',
      display: 'Nauru',
      info: 'Nauru'
    },
    {
      value: 'NP',
      display: 'Nepal',
      info: 'Nepal'
    },
    {
      value: 'NL',
      display: 'Netherlands',
      info: 'Netherlands'
    },
    {
      value: 'AN',
      display: 'Netherlands Antilles',
      info: 'Netherlands Antilles'
    },
    {
      value: 'NC',
      display: 'New Caledonia',
      info: 'New Caledonia'
    },
    {
      value: 'NZ',
      display: 'New Zealand',
      info: 'New Zealand'
    },
    {
      value: 'NI',
      display: 'Nicaragua',
      info: 'Nicaragua'
    },
    {
      value: 'NE',
      display: 'Niger',
      info: 'Niger'
    },
    {
      value: 'NG',
      display: 'Nigeria',
      info: 'Nigeria'
    },
    {
      value: 'NU',
      display: 'Niue',
      info: 'Niue'
    },
    {
      value: 'NF',
      display: 'Norfolk Island',
      info: 'Norfolk Island'
    },
    {
      value: 'MP',
      display: 'Northern Mariana Islands',
      info: 'Northern Mariana Islands'
    },
    {
      value: 'NO',
      display: 'Norway',
      info: 'Norway'
    },
    {
      value: 'OM',
      display: 'Oman',
      info: 'Oman'
    },
    {
      value: 'PK',
      display: 'Pakistan',
      info: 'Pakistan'
    },
    {
      value: 'PW',
      display: 'Palau',
      info: 'Palau'
    },
    {
      value: 'PS',
      display: 'Palestinian Territory',
      info: 'Palestinian Territory'
    },
    {
      value: 'PA',
      display: 'Panama',
      info: 'Panama'
    },
    {
      value: 'PG',
      display: 'Papua New Guinea',
      info: 'Papua New Guinea'
    },
    {
      value: 'PY',
      display: 'Paraguay',
      info: 'Paraguay'
    },
    {
      value: 'PE',
      display: 'Peru',
      info: 'Peru'
    },
    {
      value: 'PH',
      display: 'Philippines',
      info: 'Philippines'
    },
    {
      value: 'PN',
      display: 'Pitcairn',
      info: 'Pitcairn'
    },
    {
      value: 'PL',
      display: 'Poland',
      info: 'Poland'
    },
    {
      value: 'PT',
      display: 'Portugal',
      info: 'Portugal'
    },
    {
      value: 'PR',
      display: 'Puerto Rico',
      info: 'Puerto Rico'
    },
    {
      value: 'QA',
      display: 'Qatar',
      info: 'Qatar'
    },
    {
      value: 'RE',
      display: 'Reunion',
      info: 'Reunion'
    },
    {
      value: 'RO',
      display: 'Romania',
      info: 'Romania'
    },
    {
      value: 'RU',
      display: 'Russian Federation',
      info: 'Russian Federation'
    },
    {
      value: 'RW',
      display: 'Rwanda',
      info: 'Rwanda'
    },
    {
      value: 'BL',
      display: 'Saint Barthelemy',
      info: 'Saint Barthelemy'
    },
    {
      value: 'SH',
      display: 'Saint Helena',
      info: 'Saint Helena'
    },
    {
      value: 'KN',
      display: 'Saint Kitts and Nevis',
      info: 'Saint Kitts and Nevis'
    },
    {
      value: 'LC',
      display: 'Saint Lucia',
      info: 'Saint Lucia'
    },
    {
      value: 'MF',
      display: 'Saint Martin (French Part)',
      info: 'Saint Martin (French Part)'
    },
    {
      value: 'PM',
      display: 'Saint Pierre and Miquelon',
      info: 'Saint Pierre and Miquelon'
    },
    {
      value: 'VC',
      display: 'Saint Vincent and the Grenadines',
      info: 'Saint Vincent and the Grenadines'
    },
    {
      value: 'WS',
      display: 'Samoa',
      info: 'Samoa'
    },
    {
      value: 'SM',
      display: 'San Marino',
      info: 'San Marino'
    },
    {
      value: 'ST',
      display: 'Sao Tome and Principe',
      info: 'Sao Tome and Principe'
    },
    {
      value: 'SA',
      display: 'Saudi Arabia',
      info: 'Saudi Arabia'
    },
    {
      value: 'SN',
      display: 'Senegal',
      info: 'Senegal'
    },
    {
      value: 'RS',
      display: 'Serbia',
      info: 'Serbia'
    },
    {
      value: 'SC',
      display: 'Seychelles',
      info: 'Seychelles'
    },
    {
      value: 'SL',
      display: 'Sierra Leone',
      info: 'Sierra Leone'
    },
    {
      value: 'SG',
      display: 'Singapore',
      info: 'Singapore'
    },
    {
      value: 'SK',
      display: 'Slovakia',
      info: 'Slovakia'
    },
    {
      value: 'SI',
      display: 'Slovenia',
      info: 'Slovenia'
    },
    {
      value: 'SB',
      display: 'Solomon Islands',
      info: 'Solomon Islands'
    },
    {
      value: 'SO',
      display: 'Somalia',
      info: 'Somalia'
    },
    {
      value: 'ZA',
      display: 'South Africa',
      info: 'South Africa'
    },
    {
      value: 'GS',
      display: 'South Georgia and the South Sandwich Islands',
      info: 'South Georgia and the South Sandwich Islands'
    },
    {
      value: 'SS',
      display: 'South Sudan',
      info: 'South Sudan'
    },
    {
      value: 'ES',
      display: 'Spain',
      info: 'Spain'
    },
    {
      value: 'LK',
      display: 'Sri Lanka',
      info: 'Sri Lanka'
    },
    {
      value: 'SR',
      display: 'Suriname',
      info: 'Suriname'
    },
    {
      value: 'SJ',
      display: 'Svalbard and Jan Mayen Islands',
      info: 'Svalbard and Jan Mayen Islands'
    },
    {
      value: 'SZ',
      display: 'Swaziland',
      info: 'Swaziland'
    },
    {
      value: 'SE',
      display: 'Sweden',
      info: 'Sweden'
    },
    {
      value: 'CH',
      display: 'Switzerland',
      info: 'Switzerland'
    },
    {
      value: 'TW',
      display: 'Taiwan',
      info: 'Taiwan'
    },
    {
      value: 'TJ',
      display: 'Tajikistan',
      info: 'Tajikistan'
    },
    {
      value: 'TZ',
      display: 'Tanzania, United Republic of',
      info: 'Tanzania, United Republic of'
    },
    {
      value: 'TH',
      display: 'Thailand',
      info: 'Thailand'
    },
    {
      value: 'TL',
      display: 'Timor-Leste',
      info: 'Timor-Leste'
    },
    {
      value: 'TG',
      display: 'Togo',
      info: 'Togo'
    },
    {
      value: 'TK',
      display: 'Tokelau',
      info: 'Tokelau'
    },
    {
      value: 'TO',
      display: 'Tonga',
      info: 'Tonga'
    },
    {
      value: 'TT',
      display: 'Trinidad and Tobago',
      info: 'Trinidad and Tobago'
    },
    {
      value: 'TN',
      display: 'Tunisia',
      info: 'Tunisia'
    },
    {
      value: 'TR',
      display: 'Turkey',
      info: 'Turkey'
    },
    {
      value: 'TM',
      display: 'Turkmenistan',
      info: 'Turkmenistan'
    },
    {
      value: 'TC',
      display: 'Turks and Caicos Islands',
      info: 'Turks and Caicos Islands'
    },
    {
      value: 'TV',
      display: 'Tuvalu',
      info: 'Tuvalu'
    },
    {
      value: 'UG',
      display: 'Uganda',
      info: 'Uganda'
    },
    {
      value: 'UA',
      display: 'Ukraine',
      info: 'Ukraine'
    },
    {
      value: 'AE',
      display: 'United Arab Emirates',
      info: 'United Arab Emirates'
    },
    {
      value: 'GB',
      display: 'United Kingdom',
      info: 'United Kingdom'
    },
    {
      value: 'UM',
      display: 'United States Minor Outlying Islands',
      info: 'United States Minor Outlying Islands'
    },
    {
      value: 'UY',
      display: 'Uruguay',
      info: 'Uruguay'
    },
    {
      value: 'UZ',
      display: 'Uzbekistan',
      info: 'Uzbekistan'
    },
    {
      value: 'VU',
      display: 'Vanuatu',
      info: 'Vanuatu'
    },
    {
      value: 'VE',
      display: 'Venezuela',
      info: 'Venezuela'
    },
    {
      value: 'VN',
      display: 'Viet Nam',
      info: 'Viet Nam'
    },
    {
      value: 'VG',
      display: 'Virgin Islands, British',
      info: 'Virgin Islands, British'
    },
    {
      value: 'VI',
      display: 'Virgin Islands, U.S.',
      info: 'Virgin Islands, U.S.'
    },
    {
      value: 'WF',
      display: 'Wallis and Futuna Islands',
      info: 'Wallis and Futuna Islands'
    },
    {
      value: 'EH',
      display: 'Western Sahara',
      info: 'Western Sahara'
    },
    {
      value: 'YE',
      display: 'Yemen',
      info: 'Yemen'
    },
    {
      value: 'ZM',
      display: 'Zambia',
      info: 'Zambia'
    },
    {
      value: 'ZW',
      display: 'Zimbabwe',
      info: 'Zimbabwe'
  }
  ];

  var defaultErrorMessage = $filter('translate')('error_message_unknown_error');
  var defaultErrorObject = {
    failed: false,
    title: 'error_message_error_occured',
    message: $filter('translate')('error_message_failed_unknown_reason')
  };
  var originalRegistration = {};

  $scope.registrationState = function() {
    if (angular.isDefined($stateParams.registrationState)) {
      return $stateParams.registrationState;
    } else {
      return 'unknown';
    }
  };

  var profileIsGood = function() {
    var goodSetup = true;
    var deferred = $q.defer();

    if (!$scope.globalOptions.addthis_profile) {
      goodSetup = false;
      deferred.resolve(goodSetup);
    }

    $darkseid.validateAddThisProfileId($scope.globalOptions.addthis_profile)
    .then(function(data) {
      if (!data.success) {
        goodSetup = false;
      }

      deferred.resolve(goodSetup);
    });

    return deferred.promise;
  };

  var apiKeyIsGood = function() {
    var goodSetup = true;
    var deferred = $q.defer();

    if (!$scope.globalOptions.api_key) {
      goodSetup = false;
      deferred.resolve(goodSetup);
    }

    $wordpress.addThisApiKeyCheck(
      $scope.globalOptions.addthis_profile,
      $scope.globalOptions.api_key
    )
    .then(function(data) {
      if (!data.success) {
        goodSetup = false;
      }

      deferred.resolve(goodSetup);
    });

    return deferred.promise;
  };

  var setupCheck = function() {
    var result = profileIsGood().then(function(profileGood) {
      if ($scope.minimalistProPlugin() || profileGood === false) {
        return profileGood;
      } else {
        return apiKeyIsGood();
      }
    });

    return result;
  };

  var bootstrapGlobalOptions = function(globalOptions) {
    $scope.registrationFormModel = {};
    $scope.registrationFormModel.emailSubscription = true;
    $scope.registrationFormModel.country = $scope.countryOptions[0].value;
    if($wordpress.defaults('email')) {
      $scope.registrationFormModel.email = $wordpress.defaults('email');
    }

    if ($wordpress.defaults('profileName')) {
      $scope.registrationFormModel.profileName =
        $wordpress.defaults('profileName');
    } else {
      $scope.registrationFormModel.profileName =
        $filter('translate')('registration_first_profile_name_fallback');
    }

    $scope.globalOptions = globalOptions;
    $scope.loadingStatus = true;
    $scope.loadingMessage = 'progress_message_checking_registration';

    originalRegistration = {};

    if (angular.isDefined(globalOptions.addthis_profile)) {
      originalRegistration.addthis_profile = globalOptions.addthis_profile;
    }

    if (angular.isDefined(globalOptions.api_key)) {
      originalRegistration.api_key = globalOptions.api_key;
    }

    return setupCheck().then(function(setupGood) {
      if (!angular.isDefined($stateParams.registrationState) ||
        ( $stateParams.registrationState !== 'signIn' &&
          $stateParams.registrationState !== 'createAccount' &&
          $stateParams.registrationState !== 'manual')
      ) {
        if(setupGood) {
          $state.go('registration.state', {registrationState: 'registered'});
        } else {
          $state.go('registration.state', {registrationState: 'signIn'});
        }
      }
      $scope.loadingStatus = false;
    });
  };

  $wordpress.globalOptions.get().then(function(globalOptions) {
    bootstrapGlobalOptions(globalOptions);
  });

  $scope.minimalistProPlugin = function() {
    if ($scope.globalOptions.recommended_content_feature_enabled ||
        $scope.globalOptions.follow_buttons_feature_enabled ||
        $scope.globalOptions.sharing_buttons_feature_enabled
    ) {
      return false;
    } else {
      return true;
    }
  };

  $scope.signInFailed = false;
  $scope.signInErrorMessage = defaultErrorMessage;
  $scope.signInSubmit = function(valid) {
    if(!valid) {
      return;
    }

    var email = $scope.registrationFormModel.email;
    var password = $scope.registrationFormModel.password;
    $scope.loadingStatus = true;
    $scope.loadingMessage = 'progress_message_checking_login';

    $wordpress.addThisCheckLogin(email, password).then(function(data) {
      $scope.signInFailed = !data.success;

      if (data.message) {
        $scope.signInErrorMessage = data.message;
      }

      if (data.success === true) {
        $scope.loadingStatus = true;
        $scope.loadingMessage = 'progress_message_retrieving_profiles';
        $wordpress.addThisGetProfiles(email, password).then(function(data) {
          populateProfiles(data.data);
        });
      } else {
        $scope.loadingStatus = false;
      }
    });
  };

  $scope.warnOfProfileNotFoundOnAccount = false;
  $scope.profileIdFoundOnAccount = false;
  var populateProfiles = function(profiles) {
    $scope.profileIdFoundOnAccount = false;
    var createOption = {
      name: $filter('translate')('registration_select_create_new_profile'),
      pubId: ''
    };
    $scope.profiles = [];
    $scope.profiles.push(createOption);

    profiles.forEach(function(element) {
      if (element.admin === true) {
        if (element.pubId === $scope.globalOptions.addthis_profile) {
          $scope.profileIdFoundOnAccount = true;
          $scope.registrationFormModel.profile = element.pubId;
        }

        $scope.profiles.push(element);
      }
    });

    if ($scope.profileIdFoundOnAccount === false) {
      $scope.registrationFormModel.profile = createOption.pubId;
    }

    if ($scope.globalOptions.addthis_profile === '') {
      $scope.warnOfProfileNotFoundOnAccount = false;
    } else {
      $scope.warnOfProfileNotFoundOnAccount = !$scope.profileIdFoundOnAccount;
    }

    $scope.loadingStatus = false;
    $state.go('registration.state', {registrationState: 'selectProfile'});
  };

  $scope.selectProfileSubmit = function() {
    if($scope.registrationFormModel.profile !== '') {
      $scope.globalOptions.addthis_profile =
        $scope.registrationFormModel.profile;
      var email = $scope.registrationFormModel.email;
      var password = $scope.registrationFormModel.password;
      var profileId = $scope.registrationFormModel.profile;
      createApiKeyAndSave(email, password, profileId);
    } else {
      $state.go('registration.state', {registrationState: 'createProfile'});
    }
  };

  $scope.createApiKeyAndSaveStatus = defaultErrorObject;
  var createApiKeyAndSave = function(email, password, profileId) {
    $scope.createApiKeyAndSaveStatus.failed = false;
    $scope.loadingStatus = true;
    $scope.loadingMessage = 'progress_message_creating_api_key';

    var createApiKey = function() {
      var promise = $wordpress.addThisCreateApiKey(email, password, profileId)
      .then(function(data) {
        if (data.success === true) {
          $scope.globalOptions.api_key = data.apiKey;
        } else {
          $scope.createApiKeyAndSaveStatus.failed = true;
          $scope.createApiKeyAndSaveStatus.title =
            'error_message_failed_to_create_api_key';

          if (data.message ===
              'An application of this name already exists for this publisher'
          ) {
            $scope.createApiKeyAndSaveStatus.message =
              $filter('translate')('error_message_too_many_api_key_requests');
          } else {
            $scope.createApiKeyAndSaveStatus.message = data.message;
          }

          $scope.loadingStatus = false;
        }

        return data;
      });
      return promise;
    };

    var changeProfileType = function() {
      var apiKey = $scope.globalOptions.api_key;

      $scope.loadingStatus = true;
      $scope.loadingMessage = 'progress_message_changing_profile_type';

      var promise = $wordpress.addThisChangeProfileType(profileId, apiKey)
      .then(function(data) {
        if (data.success !== true) {
          $scope.createApiKeyAndSaveStatus.failed = true;
          $scope.createApiKeyAndSaveStatus.title =
            'error_message_failed_to_change_profile_type';
          $scope.createApiKeyAndSaveStatus.message = data.message;
        }

        return data;
      });
      return promise;
    };

    var checkProfileType = function() {
      $scope.loadingStatus = true;
      $scope.loadingMessage = 'progress_message_checking_profile_type';

      var profileTypeFixPromise = $darkseid.validateAddThisProfileId(profileId)
      .then(function(data) {
        if (data.success !== true) {
          $scope.createApiKeyAndSaveStatus.failed = true;
          if (data.message) {
            $scope.createAccountErrorMessage = data.message;
          }

        // change how we look up a profile type here
        } else if (data.data.type !== 'wp') {
          var createProfileTypeChangePromise = changeProfileType();
          return createProfileTypeChangePromise;
        }
      });

      return profileTypeFixPromise;
    };

    createApiKey().then(function() {
      checkProfileType().then(function() {
        $scope.loadingStatus = true;
        $scope.loadingMessage = 'progress_message_saving_registration';

        $scope.globalOptions.addthis_plugin_controls = 'AddThis';
        $wordpress.globalOptions.save().then(function(data) {
          $scope.globalOptions = data;
          if(!$scope.createApiKeyAndSaveStatus.failed) {
            $scope.successfulNewRegistration = true;
            $state.go('registration.state', {registrationState: 'registered'});
          }
          $scope.loadingStatus = false;
        });
      });
    });
  };

  $scope.createAccountShow = function() {
    $state.go('registration.state', {registrationState: 'createAccount'});
  };

  $scope.editManually = function() {
    $state.go('registration.state', {registrationState: 'manual'});
  };

  $scope.signIn = function() {
    $state.go('registration.state', {registrationState: 'signIn'});
  };

  $scope.createAccountFailed = false;
  $scope.createAccountErrorMessage = defaultErrorMessage;
  $scope.createAccountSubmit = function(valid) {
    if(!valid) {
      return;
    }

    var email = $scope.registrationFormModel.email;
    var password = $scope.registrationFormModel.password;
    var country = $scope.registrationFormModel.country;
    var newsletter = $scope.registrationFormModel.emailSubscription;
    $scope.loadingStatus = true;
    $scope.loadingMessage = 'progress_message_checking_account';

    $wordpress.addThisCreateAccount(email, password, country, newsletter)
    .then(function(data) {
      $scope.createAccountFailed = !data.success;

      if(data.success === true) {
        $scope.loadingStatus = true;
        $scope.loadingMessage = 'progress_message_retrieving_profile';
        $wordpress.addThisGetProfiles(email, password).then(function(data) {
            if (data.success) {
              $scope.globalOptions.addthis_profile = data.data[0].pubId;
              createApiKeyAndSave(
                email,
                password,
                $scope.globalOptions.addthis_profile
              );
            }
        });
      } else if (data.message) {
        $scope.loadingStatus = false;
        $scope.createAccountErrorMessage = data.message;
      }

    });
  };

  $scope.createProfileFailed = false;
  $scope.createProfileSubmit = function(valid) {
    if(!valid) {
      return;
    }
    $scope.createProfileFailed = false;

    var email = $scope.registrationFormModel.email;
    var password = $scope.registrationFormModel.password;
    var profileName = $scope.registrationFormModel.profileName;

    $scope.loadingStatus = true;
    $scope.loadingMessage = 'progress_message_creating_profile';

    $wordpress.addThisCreateProfile(email, password, profileName)
    .then(function(data) {
      if (data.success === true) {
        $scope.globalOptions.addthis_profile = data.profileId;
        createApiKeyAndSave(email, password, data.profileId);
      } else {
        $scope.createProfileFailed = true;
        $scope.createApiKeyAndSaveStatus.failed = true;
        $scope.createApiKeyAndSaveStatus.title =
          'error_message_failed_to_create_profile';
        $scope.createApiKeyAndSaveStatus.message = data.message;
        $scope.loadingStatus = false;
      }
    });
  };

  $scope.startOver = function() {
    bootstrapGlobalOptions($scope.globalOptions).then(function() {
      $state.go('registration.state', {registrationState: 'signIn'});
    });
  };

  $scope.cancel = function() {
    if (angular.isDefined(originalRegistration.addthis_profile)) {
      $scope.globalOptions.addthis_profile =
        originalRegistration.addthis_profile;
    } else if (angular.isDefined($scope.globalOptions.addthis_profile)) {
      delete $scope.globalOptions.addthis_profile;
    }

    if (angular.isDefined(originalRegistration.api_key)) {
      $scope.globalOptions.api_key = originalRegistration.api_key;
    } else if (angular.isDefined($scope.globalOptions.api_key)) {
      delete $scope.globalOptions.api_key;
    }

    $scope.startOver();
  };
});