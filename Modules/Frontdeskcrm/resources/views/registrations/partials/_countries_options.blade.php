{{--
    Country options for a guest's nationality. The canonical value is the country
    name, so the caller normalises whatever it holds through
    \Modules\Frontdeskcrm\Support\GuestNationality and passes the result as
    $selectedNationality. --
--}}

<optgroup label="Africa">
    <option value="Algeria" @selected(($selectedNationality ?? null) === "Algeria")>Algeria</option>
    <option value="Angola" @selected(($selectedNationality ?? null) === "Angola")>Angola</option>
    <option value="Benin" @selected(($selectedNationality ?? null) === "Benin")>Benin</option>
    <option value="Botswana" @selected(($selectedNationality ?? null) === "Botswana")>Botswana</option>
    <option value="Burkina Faso" @selected(($selectedNationality ?? null) === "Burkina Faso")>Burkina Faso</option>
    <option value="Burundi" @selected(($selectedNationality ?? null) === "Burundi")>Burundi</option>
    <option value="Cabo Verde" @selected(($selectedNationality ?? null) === "Cabo Verde")>Cabo Verde</option>
    <option value="Cameroon" @selected(($selectedNationality ?? null) === "Cameroon")>Cameroon</option>
    <option value="Central African Republic" @selected(($selectedNationality ?? null) === "Central African Republic")>Central African Republic</option>
    <option value="Chad" @selected(($selectedNationality ?? null) === "Chad")>Chad</option>
    <option value="Comoros" @selected(($selectedNationality ?? null) === "Comoros")>Comoros</option>
    <option value="Congo, Democratic Republic of the" @selected(($selectedNationality ?? null) === "Congo, Democratic Republic of the")>Congo, Democratic Republic of the</option>
    <option value="Congo, Republic of the" @selected(($selectedNationality ?? null) === "Congo, Republic of the")>Congo, Republic of the</option>
    <option value="Cote d'Ivoire" @selected(($selectedNationality ?? null) === "Cote d'Ivoire")>Cote d'Ivoire</option>
    <option value="Djibouti" @selected(($selectedNationality ?? null) === "Djibouti")>Djibouti</option>
    <option value="Egypt" @selected(($selectedNationality ?? null) === "Egypt")>Egypt</option>
    <option value="Equatorial Guinea" @selected(($selectedNationality ?? null) === "Equatorial Guinea")>Equatorial Guinea</option>
    <option value="Eritrea" @selected(($selectedNationality ?? null) === "Eritrea")>Eritrea</option>
    <option value="Eswatini" @selected(($selectedNationality ?? null) === "Eswatini")>Eswatini</option>
    <option value="Ethiopia" @selected(($selectedNationality ?? null) === "Ethiopia")>Ethiopia</option>
    <option value="Gabon" @selected(($selectedNationality ?? null) === "Gabon")>Gabon</option>
    <option value="Gambia" @selected(($selectedNationality ?? null) === "Gambia")>Gambia</option>
    <option value="Ghana" @selected(($selectedNationality ?? null) === "Ghana")>Ghana</option>
    <option value="Guinea" @selected(($selectedNationality ?? null) === "Guinea")>Guinea</option>
    <option value="Guinea-Bissau" @selected(($selectedNationality ?? null) === "Guinea-Bissau")>Guinea-Bissau</option>
    <option value="Kenya" @selected(($selectedNationality ?? null) === "Kenya")>Kenya</option>
    <option value="Lesotho" @selected(($selectedNationality ?? null) === "Lesotho")>Lesotho</option>
    <option value="Liberia" @selected(($selectedNationality ?? null) === "Liberia")>Liberia</option>
    <option value="Libya" @selected(($selectedNationality ?? null) === "Libya")>Libya</option>
    <option value="Madagascar" @selected(($selectedNationality ?? null) === "Madagascar")>Madagascar</option>
    <option value="Malawi" @selected(($selectedNationality ?? null) === "Malawi")>Malawi</option>
    <option value="Mali" @selected(($selectedNationality ?? null) === "Mali")>Mali</option>
    <option value="Mauritania" @selected(($selectedNationality ?? null) === "Mauritania")>Mauritania</option>
    <option value="Mauritius" @selected(($selectedNationality ?? null) === "Mauritius")>Mauritius</option>
    <option value="Morocco" @selected(($selectedNationality ?? null) === "Morocco")>Morocco</option>
    <option value="Mozambique" @selected(($selectedNationality ?? null) === "Mozambique")>Mozambique</option>
    <option value="Namibia" @selected(($selectedNationality ?? null) === "Namibia")>Namibia</option>
    <option value="Niger" @selected(($selectedNationality ?? null) === "Niger")>Niger</option>
    <option value="Nigeria" @selected(($selectedNationality ?? null) === "Nigeria")>Nigeria</option>
    <option value="Rwanda" @selected(($selectedNationality ?? null) === "Rwanda")>Rwanda</option>
    <option value="Sao Tome and Principe" @selected(($selectedNationality ?? null) === "Sao Tome and Principe")>Sao Tome and Principe</option>
    <option value="Senegal" @selected(($selectedNationality ?? null) === "Senegal")>Senegal</option>
    <option value="Seychelles" @selected(($selectedNationality ?? null) === "Seychelles")>Seychelles</option>
    <option value="Sierra Leone" @selected(($selectedNationality ?? null) === "Sierra Leone")>Sierra Leone</option>
    <option value="Somalia" @selected(($selectedNationality ?? null) === "Somalia")>Somalia</option>
    <option value="South Africa" @selected(($selectedNationality ?? null) === "South Africa")>South Africa</option>
    <option value="South Sudan" @selected(($selectedNationality ?? null) === "South Sudan")>South Sudan</option>
    <option value="Sudan" @selected(($selectedNationality ?? null) === "Sudan")>Sudan</option>
    <option value="Tanzania" @selected(($selectedNationality ?? null) === "Tanzania")>Tanzania</option>
    <option value="Togo" @selected(($selectedNationality ?? null) === "Togo")>Togo</option>
    <option value="Tunisia" @selected(($selectedNationality ?? null) === "Tunisia")>Tunisia</option>
    <option value="Uganda" @selected(($selectedNationality ?? null) === "Uganda")>Uganda</option>
    <option value="Zambia" @selected(($selectedNationality ?? null) === "Zambia")>Zambia</option>
    <option value="Zimbabwe" @selected(($selectedNationality ?? null) === "Zimbabwe")>Zimbabwe</option>
</optgroup>
<optgroup label="Asia">
    <option value="Afghanistan" @selected(($selectedNationality ?? null) === "Afghanistan")>Afghanistan</option>
    <option value="Bahrain" @selected(($selectedNationality ?? null) === "Bahrain")>Bahrain</option>
    <option value="Bangladesh" @selected(($selectedNationality ?? null) === "Bangladesh")>Bangladesh</option>
    <option value="Bhutan" @selected(($selectedNationality ?? null) === "Bhutan")>Bhutan</option>
    <option value="Brunei" @selected(($selectedNationality ?? null) === "Brunei")>Brunei</option>
    <option value="Cambodia" @selected(($selectedNationality ?? null) === "Cambodia")>Cambodia</option>
    <option value="China" @selected(($selectedNationality ?? null) === "China")>China</option>
    <option value="India" @selected(($selectedNationality ?? null) === "India")>India</option>
    <option value="Indonesia" @selected(($selectedNationality ?? null) === "Indonesia")>Indonesia</option>
    <option value="Iran" @selected(($selectedNationality ?? null) === "Iran")>Iran</option>
    <option value="Iraq" @selected(($selectedNationality ?? null) === "Iraq")>Iraq</option>
    <option value="Israel" @selected(($selectedNationality ?? null) === "Israel")>Israel</option>
    <option value="Japan" @selected(($selectedNationality ?? null) === "Japan")>Japan</option>
    <option value="Jordan" @selected(($selectedNationality ?? null) === "Jordan")>Jordan</option>
    <option value="Kazakhstan" @selected(($selectedNationality ?? null) === "Kazakhstan")>Kazakhstan</option>
    <option value="Kuwait" @selected(($selectedNationality ?? null) === "Kuwait")>Kuwait</option>
    <option value="Kyrgyzstan" @selected(($selectedNationality ?? null) === "Kyrgyzstan")>Kyrgyzstan</option>
    <option value="Laos" @selected(($selectedNationality ?? null) === "Laos")>Laos</option>
    <option value="Lebanon" @selected(($selectedNationality ?? null) === "Lebanon")>Lebanon</option>
    <option value="Malaysia" @selected(($selectedNationality ?? null) === "Malaysia")>Malaysia</option>
    <option value="Maldives" @selected(($selectedNationality ?? null) === "Maldives")>Maldives</option>
    <option value="Mongolia" @selected(($selectedNationality ?? null) === "Mongolia")>Mongolia</option>
    <option value="Myanmar" @selected(($selectedNationality ?? null) === "Myanmar")>Myanmar</option>
    <option value="Nepal" @selected(($selectedNationality ?? null) === "Nepal")>Nepal</option>
    <option value="North Korea" @selected(($selectedNationality ?? null) === "North Korea")>North Korea</option>
    <option value="Oman" @selected(($selectedNationality ?? null) === "Oman")>Oman</option>
    <option value="Pakistan" @selected(($selectedNationality ?? null) === "Pakistan")>Pakistan</option>
    <option value="Palestine" @selected(($selectedNationality ?? null) === "Palestine")>Palestine</option>
    <option value="Philippines" @selected(($selectedNationality ?? null) === "Philippines")>Philippines</option>
    <option value="Qatar" @selected(($selectedNationality ?? null) === "Qatar")>Qatar</option>
    <option value="Saudi Arabia" @selected(($selectedNationality ?? null) === "Saudi Arabia")>Saudi Arabia</option>
    <option value="Singapore" @selected(($selectedNationality ?? null) === "Singapore")>Singapore</option>
    <option value="South Korea" @selected(($selectedNationality ?? null) === "South Korea")>South Korea</option>
    <option value="Sri Lanka" @selected(($selectedNationality ?? null) === "Sri Lanka")>Sri Lanka</option>
    <option value="Syria" @selected(($selectedNationality ?? null) === "Syria")>Syria</option>
    <option value="Taiwan" @selected(($selectedNationality ?? null) === "Taiwan")>Taiwan</option>
    <option value="Tajikistan" @selected(($selectedNationality ?? null) === "Tajikistan")>Tajikistan</option>
    <option value="Thailand" @selected(($selectedNationality ?? null) === "Thailand")>Thailand</option>
    <option value="Timor-Leste" @selected(($selectedNationality ?? null) === "Timor-Leste")>Timor-Leste</option>
    <option value="Turkey" @selected(($selectedNationality ?? null) === "Turkey")>Turkey</option>
    <option value="Turkmenistan" @selected(($selectedNationality ?? null) === "Turkmenistan")>Turkmenistan</option>
    <option value="United Arab Emirates" @selected(($selectedNationality ?? null) === "United Arab Emirates")>United Arab Emirates</option>
    <option value="Uzbekistan" @selected(($selectedNationality ?? null) === "Uzbekistan")>Uzbekistan</option>
    <option value="Vietnam" @selected(($selectedNationality ?? null) === "Vietnam")>Vietnam</option>
    <option value="Yemen" @selected(($selectedNationality ?? null) === "Yemen")>Yemen</option>
</optgroup>
<optgroup label="Europe">
    <option value="Albania" @selected(($selectedNationality ?? null) === "Albania")>Albania</option>
    <option value="Andorra" @selected(($selectedNationality ?? null) === "Andorra")>Andorra</option>
    <option value="Austria" @selected(($selectedNationality ?? null) === "Austria")>Austria</option>
    <option value="Belarus" @selected(($selectedNationality ?? null) === "Belarus")>Belarus</option>
    <option value="Belgium" @selected(($selectedNationality ?? null) === "Belgium")>Belgium</option>
    <option value="Bosnia and Herzegovina" @selected(($selectedNationality ?? null) === "Bosnia and Herzegovina")>Bosnia and Herzegovina</option>
    <option value="Bulgaria" @selected(($selectedNationality ?? null) === "Bulgaria")>Bulgaria</option>
    <option value="Croatia" @selected(($selectedNationality ?? null) === "Croatia")>Croatia</option>
    <option value="Cyprus" @selected(($selectedNationality ?? null) === "Cyprus")>Cyprus</option>
    <option value="Czech Republic" @selected(($selectedNationality ?? null) === "Czech Republic")>Czech Republic</option>
    <option value="Denmark" @selected(($selectedNationality ?? null) === "Denmark")>Denmark</option>
    <option value="Estonia" @selected(($selectedNationality ?? null) === "Estonia")>Estonia</option>
    <option value="Finland" @selected(($selectedNationality ?? null) === "Finland")>Finland</option>
    <option value="France" @selected(($selectedNationality ?? null) === "France")>France</option>
    <option value="Germany" @selected(($selectedNationality ?? null) === "Germany")>Germany</option>
    <option value="Greece" @selected(($selectedNationality ?? null) === "Greece")>Greece</option>
    <option value="Hungary" @selected(($selectedNationality ?? null) === "Hungary")>Hungary</option>
    <option value="Iceland" @selected(($selectedNationality ?? null) === "Iceland")>Iceland</option>
    <option value="Ireland" @selected(($selectedNationality ?? null) === "Ireland")>Ireland</option>
    <option value="Italy" @selected(($selectedNationality ?? null) === "Italy")>Italy</option>
    <option value="Kosovo" @selected(($selectedNationality ?? null) === "Kosovo")>Kosovo</option>
    <option value="Latvia" @selected(($selectedNationality ?? null) === "Latvia")>Latvia</option>
    <option value="Liechtenstein" @selected(($selectedNationality ?? null) === "Liechtenstein")>Liechtenstein</option>
    <option value="Lithuania" @selected(($selectedNationality ?? null) === "Lithuania")>Lithuania</option>
    <option value="Luxembourg" @selected(($selectedNationality ?? null) === "Luxembourg")>Luxembourg</option>
    <option value="Malta" @selected(($selectedNationality ?? null) === "Malta")>Malta</option>
    <option value="Moldova" @selected(($selectedNationality ?? null) === "Moldova")>Moldova</option>
    <option value="Monaco" @selected(($selectedNationality ?? null) === "Monaco")>Monaco</option>
    <option value="Montenegro" @selected(($selectedNationality ?? null) === "Montenegro")>Montenegro</option>
    <option value="Netherlands" @selected(($selectedNationality ?? null) === "Netherlands")>Netherlands</option>
    <option value="North Macedonia" @selected(($selectedNationality ?? null) === "North Macedonia")>North Macedonia</option>
    <option value="Norway" @selected(($selectedNationality ?? null) === "Norway")>Norway</option>
    <option value="Poland" @selected(($selectedNationality ?? null) === "Poland")>Poland</option>
    <option value="Portugal" @selected(($selectedNationality ?? null) === "Portugal")>Portugal</option>
    <option value="Romania" @selected(($selectedNationality ?? null) === "Romania")>Romania</option>
    <option value="Russia" @selected(($selectedNationality ?? null) === "Russia")>Russia</option>
    <option value="San Marino" @selected(($selectedNationality ?? null) === "San Marino")>San Marino</option>
    <option value="Serbia" @selected(($selectedNationality ?? null) === "Serbia")>Serbia</option>
    <option value="Slovakia" @selected(($selectedNationality ?? null) === "Slovakia")>Slovakia</option>
    <option value="Slovenia" @selected(($selectedNationality ?? null) === "Slovenia")>Slovenia</option>
    <option value="Spain" @selected(($selectedNationality ?? null) === "Spain")>Spain</option>
    <option value="Sweden" @selected(($selectedNationality ?? null) === "Sweden")>Sweden</option>
    <option value="Switzerland" @selected(($selectedNationality ?? null) === "Switzerland")>Switzerland</option>
    <option value="Ukraine" @selected(($selectedNationality ?? null) === "Ukraine")>Ukraine</option>
    <option value="United Kingdom" @selected(($selectedNationality ?? null) === "United Kingdom")>United Kingdom</option>
    <option value="Vatican City" @selected(($selectedNationality ?? null) === "Vatican City")>Vatican City</option>
</optgroup>
<optgroup label="North America">
    <option value="Antigua and Barbuda" @selected(($selectedNationality ?? null) === "Antigua and Barbuda")>Antigua and Barbuda</option>
    <option value="Bahamas" @selected(($selectedNationality ?? null) === "Bahamas")>Bahamas</option>
    <option value="Barbados" @selected(($selectedNationality ?? null) === "Barbados")>Barbados</option>
    <option value="Belize" @selected(($selectedNationality ?? null) === "Belize")>Belize</option>
    <option value="Canada" @selected(($selectedNationality ?? null) === "Canada")>Canada</option>
    <option value="Costa Rica" @selected(($selectedNationality ?? null) === "Costa Rica")>Costa Rica</option>
    <option value="Cuba" @selected(($selectedNationality ?? null) === "Cuba")>Cuba</option>
    <option value="Dominica" @selected(($selectedNationality ?? null) === "Dominica")>Dominica</option>
    <option value="Dominican Republic" @selected(($selectedNationality ?? null) === "Dominican Republic")>Dominican Republic</option>
    <option value="El Salvador" @selected(($selectedNationality ?? null) === "El Salvador")>El Salvador</option>
    <option value="Grenada" @selected(($selectedNationality ?? null) === "Grenada")>Grenada</option>
    <option value="Guatemala" @selected(($selectedNationality ?? null) === "Guatemala")>Guatemala</option>
    <option value="Haiti" @selected(($selectedNationality ?? null) === "Haiti")>Haiti</option>
    <option value="Honduras" @selected(($selectedNationality ?? null) === "Honduras")>Honduras</option>
    <option value="Jamaica" @selected(($selectedNationality ?? null) === "Jamaica")>Jamaica</option>
    <option value="Mexico" @selected(($selectedNationality ?? null) === "Mexico")>Mexico</option>
    <option value="Nicaragua" @selected(($selectedNationality ?? null) === "Nicaragua")>Nicaragua</option>
    <option value="Panama" @selected(($selectedNationality ?? null) === "Panama")>Panama</option>
    <option value="Saint Kitts and Nevis" @selected(($selectedNationality ?? null) === "Saint Kitts and Nevis")>Saint Kitts and Nevis</option>
    <option value="Saint Lucia" @selected(($selectedNationality ?? null) === "Saint Lucia")>Saint Lucia</option>
    <option value="Saint Vincent and the Grenadines" @selected(($selectedNationality ?? null) === "Saint Vincent and the Grenadines")>Saint Vincent and the Grenadines</option>
    <option value="Trinidad and Tobago" @selected(($selectedNationality ?? null) === "Trinidad and Tobago")>Trinidad and Tobago</option>
    <option value="United States" @selected(($selectedNationality ?? null) === "United States")>United States</option>
</optgroup>
<optgroup label="South America">
    <option value="Argentina" @selected(($selectedNationality ?? null) === "Argentina")>Argentina</option>
    <option value="Bolivia" @selected(($selectedNationality ?? null) === "Bolivia")>Bolivia</option>
    <option value="Brazil" @selected(($selectedNationality ?? null) === "Brazil")>Brazil</option>
    <option value="Chile" @selected(($selectedNationality ?? null) === "Chile")>Chile</option>
    <option value="Colombia" @selected(($selectedNationality ?? null) === "Colombia")>Colombia</option>
    <option value="Ecuador" @selected(($selectedNationality ?? null) === "Ecuador")>Ecuador</option>
    <option value="Guyana" @selected(($selectedNationality ?? null) === "Guyana")>Guyana</option>
    <option value="Paraguay" @selected(($selectedNationality ?? null) === "Paraguay")>Paraguay</option>
    <option value="Peru" @selected(($selectedNationality ?? null) === "Peru")>Peru</option>
    <option value="Suriname" @selected(($selectedNationality ?? null) === "Suriname")>Suriname</option>
    <option value="Uruguay" @selected(($selectedNationality ?? null) === "Uruguay")>Uruguay</option>
    <option value="Venezuela" @selected(($selectedNationality ?? null) === "Venezuela")>Venezuela</option>
</optgroup>
<optgroup label="Oceania">
    <option value="Australia" @selected(($selectedNationality ?? null) === "Australia")>Australia</option>
    <option value="Fiji" @selected(($selectedNationality ?? null) === "Fiji")>Fiji</option>
    <option value="Kiribati" @selected(($selectedNationality ?? null) === "Kiribati")>Kiribati</option>
    <option value="Marshall Islands" @selected(($selectedNationality ?? null) === "Marshall Islands")>Marshall Islands</option>
    <option value="Micronesia" @selected(($selectedNationality ?? null) === "Micronesia")>Micronesia</option>
    <option value="Nauru" @selected(($selectedNationality ?? null) === "Nauru")>Nauru</option>
    <option value="New Zealand" @selected(($selectedNationality ?? null) === "New Zealand")>New Zealand</option>
    <option value="Palau" @selected(($selectedNationality ?? null) === "Palau")>Palau</option>
    <option value="Papua New Guinea" @selected(($selectedNationality ?? null) === "Papua New Guinea")>Papua New Guinea</option>
    <option value="Samoa" @selected(($selectedNationality ?? null) === "Samoa")>Samoa</option>
    <option value="Solomon Islands" @selected(($selectedNationality ?? null) === "Solomon Islands")>Solomon Islands</option>
    <option value="Tonga" @selected(($selectedNationality ?? null) === "Tonga")>Tonga</option>
    <option value="Tuvalu" @selected(($selectedNationality ?? null) === "Tuvalu")>Tuvalu</option>
    <option value="Vanuatu" @selected(($selectedNationality ?? null) === "Vanuatu")>Vanuatu</option>
</optgroup>
