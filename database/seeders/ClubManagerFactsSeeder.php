<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Real, verified facts for each club's current head coach plus updated
 * honours lists, researched from Wikipedia in August 2026. Every manager
 * name here was cross-checked against the club's actual current staff
 * (a large number of the site's original seeded manager names turned out
 * to be stale after a very active close season), and every trophy count
 * was verified by counting the actual listed years rather than trusting a
 * stated total.
 *
 * Deliberately never touches is_published or any other field - only sets
 * the specific columns each team has verified data for. founded_year is
 * only included where research corrected an existing value.
 */
class ClubManagerFactsSeeder extends Seeder
{
    public function run(): void
    {
        $facts = [
        'afc-bournemouth' => [
            'manager' => 'Marco Rose',
            'manager_facts' => 'German nationality. Appointed Bournemouth head coach in April 2026 (officially started 1 June 2026) for the 2026-27 season. Previously managed RB Leipzig (2022-2025).',
            'manager_photo_path' => 'assets/img/managers/afc-bournemouth.jpg',
            'honours_facts' => 'No major top-flight league title, FA Cup, or League Cup wins to date. Highest domestic honours: EFL Championship title 1 (2014-15); Football League Third Division title 1 (1986-87); Associate Members\' Cup 1 (1984).',
        ],
        'liverpool' => [
            'manager' => 'Andoni Iraola',
            'manager_facts' => 'Spanish nationality. Appointed Liverpool head coach in summer 2026, succeeding Arne Slot. Previously managed Bournemouth (2023-2026) and Rayo Vallecano (2020-2023).',
            'manager_photo_path' => 'assets/img/managers/liverpool.jpg',
            'honours_facts' => 'English top-flight title: 20 (1900-01, 1905-06, 1921-22, 1922-23, 1946-47, 1963-64, 1965-66, 1972-73, 1975-76, 1976-77, 1978-79, 1979-80, 1981-82, 1982-83, 1983-84, 1985-86, 1987-88, 1989-90, 2019-20, 2024-25)
FA Cup: 8 (1965, 1974, 1986, 1989, 1992, 2001, 2006, 2022)
League Cup: 10 (1981, 1982, 1983, 1984, 1995, 2001, 2003, 2012, 2022, 2024)
European Cup / UEFA Champions League: 6 (1977, 1978, 1981, 1984, 2005, 2019)
UEFA Cup / Europa League: 3 (1973, 1976, 2001)
UEFA Super Cup: 4 (1977, 2001, 2005, 2019)
FIFA Club World Cup: 1 (2019)',
        ],
        'arsenal' => [
            'manager' => 'Mikel Arteta',
            'manager_facts' => 'Spanish nationality. Appointed Arsenal manager in December 2019. Immediately before, he was assistant coach at Manchester City (2016-2019) under Pep Guardiola (his first senior head coaching job was at Arsenal).',
            'manager_photo_path' => 'assets/img/managers/arsenal.png',
            'honours_facts' => 'English top-flight title: 14 (1930-31, 1932-33, 1933-34, 1934-35, 1937-38, 1947-48, 1952-53, 1970-71, 1988-89, 1990-91, 2003-04, 2004-05, 2005-06, 2025-26)
FA Cup: 14 (1930, 1936, 1950, 1971, 1979, 1993, 1998, 2002, 2003, 2005, 2014, 2015, 2017, 2020)
League Cup: 2 (1987, 1993)
European Cup Winners\' Cup: 1 (1994)
Inter-Cities Fairs Cup: 1 (1970)',
        ],
        'aston-villa' => [
            'manager' => 'Unai Emery',
            'manager_facts' => 'Spanish nationality. Appointed Aston Villa manager in October/November 2022. Previously managed Villarreal (2020-2022).',
            'manager_photo_path' => 'assets/img/managers/aston-villa.jpg',
            'honours_facts' => 'English top-flight title: 7 (1893-94, 1895-96, 1896-97, 1898-99, 1899-1900, 1909-10, 1980-81)
FA Cup: 7 (1887, 1895, 1897, 1905, 1913, 1920, 1957)
League Cup: 5 (1961, 1975, 1977, 1994, 1996)
European Cup: 1 (1982)
UEFA Super Cup: 1 (1982)
UEFA Europa League: 1 (2026)',
        ],
        'brentford' => [
            'manager' => 'Keith Andrews',
            'manager_facts' => 'Irish nationality. Appointed Brentford head coach in June 2025. Immediately before, he was Brentford\'s set-piece coach (2024) and prior to that on Sheffield United\'s backroom staff under Chris Wilder (from December 2023).',
            'honours_facts' => 'No major top-flight league title, FA Cup, or League Cup wins. Highest honours: Second Division/Championship (level 2) title 1 (1934-35); Third Division South title 1 (1932-33); Fourth Division title 1 (1962-63); Championship play-off winners 2021 (route to first-ever Premier League promotion).',
        ],
        'brighton-hove-albion' => [
            'manager' => 'Fabian Hurzeler',
            'manager_facts' => 'German nationality. Appointed Brighton head coach in June 2024. Previously managed FC St. Pauli (permanent head coach December 2022-2024).',
            'manager_photo_path' => 'assets/img/managers/brighton-hove-albion.jpg',
            'honours_facts' => 'No top-flight league title or major domestic cup (FA Cup/League Cup) win. FA Cup runners-up 1983 (their only major cup final). FA Charity Shield 1 (1910, shared). Lower-division titles: Southern League 1 (1909-10); Third Division South/League One 3 (1957-58, 2001-02, 2010-11); Fourth Division 2 (1964-65, 2000-01). Highest-ever top-flight finish: 6th in Premier League, 2022-23, first European qualification in club history.',
        ],
        'burnley' => [
            'manager' => 'Nicky Hayen',
            'manager_facts' => 'Belgian nationality. Appointed Burnley head coach on 10 July 2026 (three-year deal). Previously managed Genk (2025-2026) and Club Brugge (2024-2025).',
            'honours_facts' => 'English top-flight title: 2 (1920-21, 1959-60)
FA Cup: 1 (1914)
FA Charity Shield: 2 (1960, 1973)',
        ],
        'chelsea' => [
            'manager' => 'Xabi Alonso',
            'manager_facts' => 'Spanish nationality. Appointed Chelsea manager in May 2026, taking charge on 1 July 2026 (four-year deal). Previously managed Real Madrid (2025-2026) and Bayer Leverkusen (2022-2025).',
            'manager_photo_path' => 'assets/img/managers/chelsea.jpg',
            'honours_facts' => 'English top-flight title: 6 (1954-55, 2004-05, 2005-06, 2009-10, 2014-15, 2016-17)
FA Cup: 8 (1970, 1997, 2000, 2007, 2009, 2010, 2012, 2018)
League Cup: 5 (1965, 1998, 2005, 2007, 2015)
UEFA Champions League: 2 (2012, 2021)
UEFA Europa League: 2 (2013, 2019)
UEFA Cup Winners\' Cup: 2 (1971, 1998)
UEFA Super Cup: 2 (1998, 2021)
UEFA Conference League: 1 (2025)
FIFA Club World Cup: 2 (2021, 2025)',
        ],
        'crystal-palace' => [
            'manager' => 'Pierre Sage',
            'manager_facts' => 'French nationality. Appointed Crystal Palace manager in June 2026 (three-year deal). Previously managed RC Lens (June 2025-June 2026) and Olympique Lyonnais (November 2023-January 2025).',
            'honours_facts' => 'No English top-flight (Premier League/old First Division) title. Football League First Division (second tier) title: 1 (1993-94); Second Division title: 1 (1978-79); Third Division title: 1 (1920-21). FA Cup: 1 (2025) - the club\'s first-ever major trophy. FA Community Shield: 1 (2025). UEFA Conference League: 1 (2025-26) - the club\'s first European trophy. Highest-ever top-flight league finish: 3rd place (1990-91).',
        ],
        'everton' => [
            'manager' => 'David Moyes',
            'manager_facts' => 'Scottish nationality. Returned to Everton as manager on 11 January 2025 (his second spell at the club). Immediately before, he managed West Ham United (2019-2024).',
            'manager_photo_path' => 'assets/img/managers/everton.jpg',
            'honours_facts' => 'English top-flight title: 9 (1890-91, 1914-15, 1927-28, 1931-32, 1938-39, 1962-63, 1969-70, 1984-85, 1986-87)
FA Cup: 5 (1906, 1933, 1966, 1984, 1995)
European Cup Winners\' Cup: 1 (1985)',
        ],
        'fulham' => [
            'manager' => 'Alvaro Arbeloa',
            'manager_facts' => 'Spanish nationality. Appointed Fulham head coach on 7 July 2026 (three-year deal). Immediately before, he was Real Madrid first-team head coach (January-June 2026), having been promoted from Real Madrid Castilla/B team manager (May 2025-January 2026).',
            'manager_photo_path' => 'assets/img/managers/fulham.jpg',
            'honours_facts' => 'No top-flight (Premier League/old First Division) title. Second-tier titles: 3 (1948-49 Second Division, 2000-01 Second Division, 2021-22 Championship). Third-tier titles: 2 (1931-32 Third Division South, 1998-99 Second Division [old numbering]). Championship play-off winners: 2018, 2020. FA Cup runners-up 1975 (never won the FA Cup). UEFA Europa League runners-up 2010. UEFA Intertoto Cup: 1 (2002). Southern League First Division: 2 (1905-06, 1906-07).',
        ],
        'leeds-united' => [
            'manager' => 'Daniel Farke',
            'manager_facts' => 'German nationality. Appointed Leeds United manager on 4 July 2023. Previously managed Borussia Monchengladbach (2022-2023).',
            'manager_photo_path' => 'assets/img/managers/leeds-united.jpg',
            'honours_facts' => 'English top-flight title: 3 (1968-69, 1973-74, 1991-92)
FA Cup: 1 (1972)
League Cup: 1 (1968)
Inter-Cities Fairs Cup: 2 (1968, 1971)
FA Charity Shield: 2 (1969, 1992)',
        ],
        'manchester-united' => [
            'manager' => 'Michael Carrick',
            'manager_facts' => 'English nationality. Returned to Manchester United as head coach on 13 January 2026 (initially to end of season, made permanent 22 May 2026 through 2028). Immediately before, he managed Middlesbrough. He had also twice been Man United\'s caretaker manager previously (2018, 2021).',
            'manager_photo_path' => 'assets/img/managers/manchester-united.jpg',
            'honours_facts' => 'English top-flight title: 20 (1907-08, 1910-11, 1951-52, 1955-56, 1956-57, 1964-65, 1966-67, 1992-93, 1993-94, 1995-96, 1996-97, 1998-99, 1999-2000, 2000-01, 2002-03, 2006-07, 2007-08, 2008-09, 2010-11, 2012-13)
FA Cup: 13 (1909, 1948, 1963, 1977, 1983, 1985, 1990, 1994, 1996, 1999, 2004, 2016, 2024)
League Cup: 6 (1992, 2006, 2009, 2010, 2017, 2023)
European Cup / UEFA Champions League: 3 (1968, 1999, 2008)
UEFA Cup Winners\' Cup: 1 (1991)
UEFA Europa League: 1 (2017)
UEFA Super Cup: 1 (1991)
Intercontinental Cup: 1 (1999)
FIFA Club World Cup: 1 (2008)',
        ],
        'newcastle-united' => [
            'manager' => 'Matthias Jaissle',
            'manager_facts' => 'German nationality. Appointed Newcastle United head coach on 5 August 2026 (four-year deal). Previously managed Al-Ahli Saudi FC (2023-2026) and Red Bull Salzburg (2021-2023).',
            'manager_photo_path' => 'assets/img/managers/newcastle-united.jpg',
            'honours_facts' => 'English top-flight title: 4 (1904-05, 1906-07, 1908-09, 1926-27)
FA Cup: 6 (1910, 1924, 1932, 1951, 1952, 1955)
League Cup: 1 (2025, beat Liverpool in the final - the club\'s first domestic trophy since 1955)
Inter-Cities Fairs Cup: 1 (1969)
UEFA Intertoto Cup: 1 (2006)
FA Charity Shield: 1 (1909)',
        ],
        'nottingham-forest' => [
            'manager' => 'Oliver Glasner',
            'manager_facts' => 'Austrian nationality. Appointed Nottingham Forest head coach on 6 July 2026, succeeding Vitor Pereira. Immediately before, he managed Crystal Palace (February 2024-June 2026), where he won the club\'s first-ever FA Cup, a Community Shield, and the UEFA Conference League.',
            'manager_photo_path' => 'assets/img/managers/nottingham-forest.jpg',
            'honours_facts' => 'English top-flight title: 1 (1977-78)
FA Cup: 2 (1898, 1959)
League Cup: 4 (1978, 1979, 1989, 1990)
European Cup / UEFA Champions League: 2 (1979, 1980)
UEFA Super Cup: 1 (1979)
FA Charity Shield: 1 (1978, shared)',
        ],
        'sunderland' => [
            'manager' => 'Regis Le Bris',
            'manager_facts' => 'French nationality. Appointed Sunderland head coach on 1 July 2024. Previously managed FC Lorient (2022-2024).',
            'honours_facts' => 'English top-flight title: 6 (1892, 1893, 1895, 1902, 1913, 1936)
FA Cup: 2 (1937, 1973)
FA Charity Shield: 1 (1936)',
        ],
        'tottenham-hotspur' => [
            'manager' => 'Roberto De Zerbi',
            'manager_facts' => 'Italian nationality. Appointed Tottenham Hotspur manager on 31 March 2026 (after a period as interim under Igor Tudor following Thomas Frank\'s sacking). Immediately before, he managed Marseille (2024-2026).',
            'manager_photo_path' => 'assets/img/managers/tottenham-hotspur.jpg',
            'honours_facts' => 'English top-flight title: 2 (1950-51, 1960-61)
FA Cup: 8 (1901, 1921, 1961, 1962, 1967, 1981, 1982, 1991)
League Cup: 4 (1971, 1973, 1999, 2008)
UEFA Cup Winners\' Cup: 1 (1963)
UEFA Cup / Europa League: 3 (1972, 1984, 2025)',
        ],
        'west-ham-united' => [
            'manager' => 'Nuno Espirito Santo',
            'manager_facts' => 'Portuguese nationality. Appointed West Ham United manager on 27 September 2025 (three-year deal). Immediately before, he managed Nottingham Forest (2023-2025).',
            'manager_photo_path' => 'assets/img/managers/west-ham-united.jpg',
            'honours_facts' => 'No top-flight (Premier League/old First Division) title. FA Cup: 3 (1964, 1975, 1980). European Cup Winners\' Cup: 1 (1965). UEFA Europa Conference League: 1 (2023) - first European trophy in 58 years and first major trophy since 1980. UEFA Intertoto Cup: 1 (1999). Highest-ever top-flight league finish: 3rd place (1985-86).',
        ],
        'wolverhampton-wanderers' => [
            'manager' => 'Cesar Peixoto',
            'manager_facts' => 'Portuguese nationality. Appointed Wolves head coach on 15 June 2026 (two-year deal). Previously managed Gil Vicente (led them to 6th in the Primeira Liga).',
            'manager_photo_path' => 'assets/img/managers/wolverhampton-wanderers.png',
            'honours_facts' => 'English top-flight title: 3 (1953-54, 1957-58, 1958-59)
FA Cup: 4 (1893, 1908, 1949, 1960)
League Cup: 2 (1974, 1980)
Second-tier (Championship) titles since dropping from top flight: 2008-09, 2017-18 (both led to promotion back to the Premier League)',
        ],
        'real-madrid' => [
            'manager' => 'José Mourinho',
            'manager_facts' => 'Portuguese nationality. Appointed Real Madrid head coach in June 2026, taking charge in July 2026 (his second spell at the club, having previously managed Real Madrid 2010-2013). Previously managed Benfica (2025-2026).',
            'manager_photo_path' => 'assets/img/managers/real-madrid.jpg',
            'honours_facts' => 'La Liga title: 36 (1931-32, 1932-33, 1953-54, 1954-55, 1956-57, 1957-58, 1960-61, 1961-62, 1962-63, 1963-64, 1964-65, 1966-67, 1967-68, 1968-69, 1971-72, 1974-75, 1975-76, 1977-78, 1978-79, 1979-80, 1985-86, 1986-87, 1987-88, 1988-89, 1989-90, 1994-95, 1996-97, 2000-01, 2002-03, 2006-07, 2007-08, 2011-12, 2016-17, 2019-20, 2021-22, 2023-24)
Copa del Rey: 20 (1905, 1906, 1907, 1908, 1917, 1934, 1936, 1946, 1947, 1961-62, 1969-70, 1973-74, 1974-75, 1979-80, 1981-82, 1988-89, 1992-93, 2010-11, 2013-14, 2022-23)
European Cup / UEFA Champions League: 15 (1955-56, 1956-57, 1957-58, 1958-59, 1959-60, 1965-66, 1997-98, 1999-2000, 2001-02, 2013-14, 2015-16, 2016-17, 2017-18, 2021-22, 2023-24)
Supercopa de España: 13 (1988, 1989, 1990, 1993, 1997, 2001, 2003, 2008, 2012, 2017, 2020, 2022, 2024)
UEFA Super Cup: 6 (2002, 2014, 2016, 2017, 2022, 2024)
Worldwide club titles (FIFA Club World Cup, Intercontinental Cup, FIFA Intercontinental Cup combined): 9 (Intercontinental Cup 1960, 1998, 2002; FIFA Club World Cup 2014, 2016, 2017, 2018, 2022; FIFA Intercontinental Cup 2024)',
        ],
        'barcelona' => [
            'manager' => 'Hansi Flick',
            'manager_facts' => 'German nationality. Appointed Barcelona manager on 29 May 2024, with a contract extension since agreed through 2028. Previously managed the Germany national team (2021-2023).',
            'manager_photo_path' => 'assets/img/managers/barcelona.jpg',
            'honours_facts' => 'La Liga title: 29 (1929, 1944-45, 1947-48, 1948-49, 1951-52, 1952-53, 1958-59, 1959-60, 1973-74, 1984-85, 1990-91, 1991-92, 1992-93, 1993-94, 1997-98, 1998-99, 2004-05, 2005-06, 2008-09, 2009-10, 2010-11, 2012-13, 2014-15, 2015-16, 2017-18, 2018-19, 2022-23, 2024-25, 2025-26)
Copa del Rey: 32 (1910, 1912, 1913, 1920, 1922, 1925, 1926, 1928, 1942, 1951, 1952, 1952-53, 1957, 1958-59, 1962-63, 1967-68, 1970-71, 1977-78, 1980-81, 1982-83, 1987-88, 1989-90, 1996-97, 1997-98, 2008-09, 2011-12, 2014-15, 2015-16, 2016-17, 2017-18, 2020-21, 2024-25)
UEFA Champions League / European Cup: 5 (1991-92, 2005-06, 2008-09, 2010-11, 2014-15)
Supercopa de España: 16 (1983, 1991, 1992, 1994, 1996, 2005, 2006, 2009, 2010, 2011, 2013, 2016, 2018, 2023, 2025, 2026)
UEFA Cup Winners\' Cup: 4 (1978-79, 1981-82, 1988-89, 1996-97)
FIFA Club World Cup: 3 (2009, 2011, 2015)',
        ],
        'atletico-madrid' => [
            'manager' => 'Diego Simeone',
            'manager_facts' => 'Argentine nationality. Appointed Atletico Madrid manager on 23 December 2011. Previously managed Racing Club (2011) and Catania (2011).',
            'manager_photo_path' => 'assets/img/managers/atletico-madrid.jpg',
            'honours_facts' => 'La Liga title: 11 (1939-40, 1940-41, 1949-50, 1950-51, 1965-66, 1969-70, 1972-73, 1976-77, 1995-96, 2013-14, 2020-21)
Copa del Rey: 10 (1959-60, 1960-61, 1964-65, 1971-72, 1975-76, 1984-85, 1990-91, 1991-92, 1995-96, 2012-13)
UEFA Europa League: 3 (2009-10, 2011-12, 2017-18)
Supercopa de España: 2 (1985, 2014)
UEFA Super Cup: 3 (2010, 2012, 2018)',
        ],
        'athletic-bilbao' => [
            'manager' => 'Edin Terzić',
            'manager_facts' => 'German-Croatian nationality (holds Croatian citizenship). Appointed Athletic Bilbao head coach on 5 May 2026, ahead of the 2026-27 season. Previously managed Borussia Dortmund (2022-2024).',
            'manager_photo_path' => 'assets/img/managers/athletic-bilbao.jpg',
            'honours_facts' => 'La Liga title: 8 (1929-30, 1930-31, 1933-34, 1935-36, 1942-43, 1955-56, 1982-83, 1983-84)
Copa del Rey: 24 (1903, 1904, 1910, 1911, 1914, 1915, 1916, 1921, 1923, 1930, 1931, 1932, 1933, 1943, 1944, 1944-45, 1949-50, 1955, 1956, 1958, 1969, 1972-73, 1983-84, 2023-24)
Supercopa de España: 3 (1984, 2015, 2021)',
        ],
        'real-sociedad' => [
            'manager' => 'Pellegrino Matarazzo',
            'manager_facts' => 'American nationality. Appointed Real Sociedad manager on 20 December 2025. Previously managed TSG Hoffenheim (2023-2024).',
            'honours_facts' => 'La Liga title: 2 (1980-81, 1981-82)
Copa del Rey: 4 (1909, 1986-87, 2019-20, 2025-26)
Supercopa de España: 1 (1982)',
        ],
        'celta-vigo' => [
            'manager' => 'Claudio Giráldez',
            'manager_facts' => 'Spanish nationality. Appointed Celta Vigo first-team manager in 2024. Previously managed Celta Vigo\'s B team (2022-2024).',
            'honours_facts' => 'Celta Vigo have never won La Liga, the Copa del Rey, or a major European trophy. Their major honours are lower-division/domestic: Segunda División champions: 3 (1935-36, 1981-82, 1991-92); Segunda División B champions: 1 (1980-81); Tercera División champions: 1 (1930-31); Copa del Rey runners-up (not won): 3 (1947-48, 1993-94, 2000-01)',
        ],
        'deportivo-alaves' => [
            'manager' => 'Quique Sánchez Flores',
            'manager_facts' => 'Spanish nationality. Appointed Deportivo Alavés head coach on 3 March 2026, replacing Eduardo Coudet. Previously managed Sevilla (2023-24) and, earlier in his career, Valencia, Atletico Madrid, Getafe, Espanyol, Benfica, and Watford.',
            'manager_photo_path' => 'assets/img/managers/deportivo-alaves.jpg',
            'honours_facts' => 'Alaves have never won La Liga, the Copa del Rey (runners-up once), or a major European trophy (UEFA Cup runners-up once). Their major honours are lower-division: Segunda División champions: 4 (1929-30, 1953-54, 1997-98, 2015-16); Copa del Rey runners-up (not won): 1 (2016-17); UEFA Cup runners-up (not won): 1 (2000-01)',
        ],
        'espanyol' => [
            'manager' => 'Manolo González',
            'manager_facts' => 'Spanish nationality. Appointed Espanyol first-team manager on 12 March 2024, and renewed his contract through 2027 in July 2025. Previously managed Espanyol\'s B team (2023-2024).',
            'honours_facts' => 'Espanyol have never won La Liga or a major European trophy (twice UEFA Cup runners-up, 1988 and 2007). Their major honour is the Copa del Rey: 4 (1928-29, 1940, 1999-2000, 2005-06). Also Segunda División champions: 2 (1993-94, 2020-21)',
        ],
        'getafe' => [
            'manager' => 'José Bordalás',
            'manager_facts' => 'Spanish nationality. Reappointed Getafe head coach on 29 April 2023 (having previously managed the club 2016-2021). Immediately prior to this reappointment he managed Valencia CF (2021-2022).',
            'manager_photo_path' => 'assets/img/managers/getafe.png',
            'honours_facts' => 'Getafe have never won La Liga or the Copa del Rey (runners-up twice) and have no major European trophy. Copa del Rey runners-up (not won): 2 (2006-07, 2007-08); Segunda División B champions: 1 (1998-99)',
            'founded_year' => 1983, // '1983 (DB has 1946; Wikipedia\'s infobox gives Getafe CF\'s official founding date as 8 July 1983 - the 1946 date likely refers to a distinct, earlier amateur predecessor club, Getafe Deportivo, rather than the current Getafe CF entity)',
        ],
        'girona' => [
            'manager' => 'Quique Álvarez',
            'manager_facts' => 'Spanish nationality (full name Enrique Álvarez Sanjuán). Appointed Girona FC head coach on 1 July 2026, promoted from the club\'s B team. Previously managed Girona FC B (2024-2026).',
            'honours_facts' => 'Girona have never won La Liga, the Copa del Rey, or a major European trophy. Their major honours are lower-division/regional: Segunda División B champions: 1 (2007-08); Tercera División champions: 5 (1933-34, 1947-48, 1954-55, 1988-89, 2005-06); Supercopa de Catalunya: 1 (2019); Copa Catalunya: 1 (2025)',
        ],
        'las-palmas' => [
            'manager' => 'Rubén de la Barrera',
            'manager_facts' => 'Spanish nationality. Appointed UD Las Palmas head coach on 22 June 2026, on a one-year contract. Previously managed Cultural Leonesa (February-May 2026).',
            'manager_photo_path' => 'assets/img/managers/las-palmas.png',
            'honours_facts' => 'Las Palmas have never won La Liga (runners-up once) or the Copa del Rey (runners-up once). Segunda División champions: 4 (1953-54, 1963-64, 1984-85, 1999-2000); Segunda División B champions: 2 (1992-93, 1995-96); La Liga runners-up (not won): 1 (1968-69); Copa del Rey runners-up (not won): 1 (1977-78)',
        ],
        'leganes' => [
            'manager' => 'Rubén Albés',
            'manager_facts' => 'Spanish nationality. Appointed CD Leganés head coach on 15 June 2026, for the 2026-27 season. Previously managed Umm Salal SC in Qatar (February-June 2026).',
            'honours_facts' => 'Leganes have never won La Liga or the Copa del Rey and have no major European trophy. Segunda División champions: 1 (2023-24); Segunda División B champions: 1 (1992-93); Tercera División champions: 1 (1985-86)',
        ],
        'mallorca' => [
            'manager' => 'Luis García Fernández',
            'manager_facts' => 'Spanish nationality, born in Oviedo. Appointed RCD Mallorca head coach on 22 June 2026. Previously managed UD Las Palmas (June 2025-June 2026) and, earlier, the Qatar national team (Dec 2024-Apr 2025) and Espanyol (Apr-Nov 2023).',
            'manager_photo_path' => 'assets/img/managers/mallorca.png',
            'honours_facts' => 'Mallorca have never won La Liga. Copa del Rey: 1 (2002-03; also runners-up 1990-91, 1997-98, 2023-24); Supercopa de España: 1 (1998; runners-up 2003); Segunda División champions: 2 (1959-60, 1964-65) plus play-off winners 2019; Segunda División B champions: 2 (1980-81, 2017-18); UEFA Cup Winners\' Cup runners-up (not won): 1 (1998-99)',
        ],
        'osasuna' => [
            'manager' => 'Luis Miguel Ramis',
            'manager_facts' => 'Spanish nationality. Appointed CA Osasuna head coach on 10 June 2026, on a two-year deal. Previously managed Burgos CF (October 2024-June 2026).',
            'manager_photo_path' => 'assets/img/managers/osasuna.jpg',
            'honours_facts' => 'Osasuna have never won a major national trophy. Copa del Rey runners-up (not won): 2 (2005, 2023). Best La Liga finishes: 4th place (1990-91, 2005-06)',
        ],
        'rayo-vallecano' => [
            'manager' => 'Beñat San José',
            'manager_facts' => 'Spanish nationality, born in San Sebastián. Appointed Rayo Vallecano head coach on 18 June 2026. Previously managed SD Eibar (February 2025-June 2026).',
            'manager_photo_path' => 'assets/img/managers/rayo-vallecano.jpg',
            'honours_facts' => 'Rayo Vallecano have never won La Liga or the Copa del Rey. Segunda División champions: 1 (2017-18); UEFA Conference League runners-up (not won): 1 (2025-26, lost the final 1-0 to Crystal Palace)',
        ],
        'real-betis' => [
            'manager' => 'Manuel Pellegrini',
            'manager_facts' => 'Chilean nationality. Appointed Real Betis manager on 9 July 2020, and extended his contract to 2028 on 27 November 2025. Previously managed West Ham United (2018-2019).',
            'manager_photo_path' => 'assets/img/managers/real-betis.jpg',
            'honours_facts' => 'La Liga title: 1 (1934-35)
Copa del Rey: 3 (1976-77, 2004-05, 2021-22)
UEFA Conference League runners-up (not won): 1 (2024-25)
Segunda División champions: 7 (1931-32, 1941-42, 1957-58, 1970-71, 1973-74, 2010-11, 2014-15)',
        ],
        'real-valladolid' => [
            'manager' => 'Fran Escribá',
            'manager_facts' => 'Spanish nationality, born in Valencia. Appointed Real Valladolid manager on 16 February 2026, and agreed to continue for 2026-27. Previously managed Granada CF (September 2024-May 2025).',
            'manager_photo_path' => 'assets/img/managers/real-valladolid.png',
            'honours_facts' => 'Valladolid have never won La Liga or the Copa del Rey and have no major European trophy. Segunda División champions: 3 (1947-48, 1958-59, 2006-07); Tercera División champions: 1 (1933-34); Copa de la Liga: 1 (1984)',
        ],
        'sevilla' => [
            'manager' => 'Luis García Plaza',
            'manager_facts' => 'Spanish nationality, born in Madrid. Appointed Sevilla FC head coach on 24 March 2026, replacing the dismissed Matías Almeyda. Previously managed Deportivo Alavés (May 2022-December 2024).',
            'manager_photo_path' => 'assets/img/managers/sevilla.jpg',
            'honours_facts' => 'La Liga title: 1 (1945-46)
Copa del Rey: 5 (1935, 1939, 1947-48, 2006-07, 2009-10)
Supercopa de España: 1 (2007)
UEFA Cup / UEFA Europa League: 7 - a competition record (2005-06, 2006-07, 2013-14, 2014-15, 2015-16, 2019-20, 2022-23)
UEFA Super Cup: 1 (2006)',
        ],
        'valencia' => [
            'manager' => 'Carlos Corberán',
            'manager_facts' => 'Spanish nationality, born in Cheste, Valencia. Appointed Valencia CF head coach on 24 December 2024, signing a 3-year deal to manage his boyhood club. Previously managed West Bromwich Albion (2022-2024).',
            'honours_facts' => 'La Liga title: 6 (1941-42, 1943-44, 1946-47, 1970-71, 2001-02, 2003-04)
Copa del Rey: 8 (1941, 1948-49, 1954, 1966-67, 1978-79, 1998-99, 2007-08, 2018-19)
Supercopa de España: 1 (1999)
UEFA Cup / UEFA Europa League: 1 (2003-04)
UEFA Cup Winners\' Cup: 1 (1979-80)
UEFA Super Cup: 2 (1980, 2004)',
        ],
        'villarreal' => [
            'manager' => 'Iñigo Pérez',
            'manager_facts' => 'Spanish nationality. Appointed Villarreal CF head coach on 1 June 2026, on a three-year contract, replacing Marcelino García Toral. Previously managed Rayo Vallecano (February 2024-June 2026).',
            'manager_photo_path' => 'assets/img/managers/villarreal.jpg',
            'honours_facts' => 'Villarreal have never won La Liga or the Copa del Rey. Their major honour is the UEFA Europa League: 1 (2020-21). Also UEFA Intertoto Cup: 2 (2003, 2004); Tercera División champions: 1 (1969-70)',
        ],
        'manchester-city' => [
            'manager' => 'Enzo Maresca',
            'manager_facts' => 'Italian nationality. Appointed Manchester City manager on 29 June 2026, replacing Pep Guardiola. Previously managed Chelsea (2024-2026) and Leicester City (2023-2024), winning the Championship title with Leicester in 2023-24.',
            'manager_photo_path' => 'assets/img/managers/manchester-city.jpg',
            'honours_facts' => 'English top-flight title: 10 (1936-37, 1967-68, 2011-12, 2013-14, 2017-18, 2018-19, 2020-21, 2021-22, 2022-23, 2023-24)
FA Cup: 6 (1904, 1934, 1956, 1969, 2011, 2023)
League Cup: 8 (1970, 1976, 2014, 2016, 2018, 2019, 2020, 2021)
UEFA Champions League: 1 (2023)
European Cup Winners\' Cup: 1 (1970)',
        ],
        ];

        foreach ($facts as $slug => $data) {
            $team = Team::where('slug', $slug)->first();

            if (! $team) {
                $this->command?->warn("Skipped {$slug} - no team with that slug found.");
                continue;
            }

            $team->update($data);
            $this->command?->info("Updated {$team->name}.");
        }
    }
}