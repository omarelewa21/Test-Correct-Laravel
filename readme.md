## test-correct Laravel backend.

- #select count(*) as aantal, DATE_FORMAT(created_at,'%Y-%m') as maand from questions GROUP BY DATE_FORMAT(created_at, '%Y-%m') order by maand #vraag items groei
- #select count(*) from users where updated_at >= '2019-01-01' AND users.id IN (select user_id from teachers) # aantal ingelogde docenten accounts sinds 1 januari 2019
- #select count(*) from users where deleted_at is null AND users.id IN (select user_id from teachers) # totaal aantal actieve docenten accounts
- #select count(*) from users where updated_at >= '2019-01-01' AND users.id IN (select user_id from students) # aantal ingelogde studenten accounts sinds 1 januari 2019
- #select count(*) from users where deleted_at is null AND users.id IN (select user_id from students) # totaal aantal actieve studenten accounts
- #select count(*) as aantal, DATE_FORMAT(time_start,'%Y-%m') as maand from test_takes GROUP BY DATE_FORMAT(time_start, '%Y-%m') order by maand #toets afname per maand
- #select count(*) as aantal, DATE_FORMAT(time_start,'%Y-%m-%d') as dag from test_takes where DATE_FORMAT(time_start,'%Y-%m-%d') > '2016-01-01' GROUP BY DATE_FORMAT(time_start, '%Y-%m-%d') order by dag #toetsafname per dag
- #select sum(aantal) as total, maand from (select count(*) as aantal, DATE_FORMAT(updated_at,'%Y-%m') as maand from test_participants where heartbeat_at is not null AND test_take_status_id >2 AND deleted_at is null GROUP BY DATE_FORMAT(updated_at, '%Y-%m'), user_id order by maand) as t group by maand order by maand 
 #toets deelname per maand
- #select sum(aantal) as totaal, vak from (select count(*) as aantal, subjects.`name` as vak from questions left join subjects on subjects.id = questions.subject_id group by subject_id order by subjects.name) as t group by vak order by vak # vraagitems per vak
- #select count(*) as aantal, subjects.name as vak from test_takes inner join tests on tests.id = test_takes.test_id inner join subjects on subjects.id = tests.subject_id group by subject_id order by vak) as t group by vak order by vak # toetsafnames per vak
- #select count(*) as aantal from (select count(*) as aantal from test_questions group by question_id) as t where t.aantal >=1 # vraagitems minimaal 1x gebruikt in toets


- docenten activiteiten export
select 
	distinct
	users.name_first as voornaam, 
	users.name_suffix as tussenvoegsel, 
	users.name as achternaam,
	users.username as email,
	school_locations.name,
	school_locations.customer_code,
	sections.name as sectie,
	users.created_at as aanmaakdatum,
	(select min(time_start) from test_takes where user_id = users.`id`) as eerstgeplandetoets,
	(select max(time_start) from test_takes where user_id = users.`id`) as laatstgeplandetoets,
	(select count(*) from test_takes where user_id = users.id) as afgenomentoetsen,
	(select count(*) from test_takes where user_id = users.id AND is_discussed=1) as besprokentoetsen
from 
	users 
	left join teachers on teachers.user_id = users.id 
	left join subjects on subjects.id = teachers.subject_id
	left join sections on sections.id = subjects.section_id
	left join school_locations on school_locations.id = users.school_location_id
where 
	users.created_at >= '2016-01-01' AND 
	users.id  IN (select user_id from teachers) AND
	users.username not like '%test-correct.nl' AND
	users.username not like '%vervallen%'
order by
	school_locations.name,
	users.username

- churn rate data (let op dure query)
select 
	users.id,
	users.created_at as creation_date,
	(select min(created_at) from login_logs where user_id = users.id) as first_login,
	(select min(created_at) from tests where tests.author_id = users.id AND tests.demo = 0) as first_test_created,
	(select min(DATE_FORMAT(time_start,'%Y-%m-%d')) from test_takes where test_takes.user_id = users.id AND test_takes.demo = 0) as first_test_take,
	(select min(updated_at) from test_takes where test_takes.user_id = users.id AND test_take_status_id = 9 AND test_takes.demo = 0) as first_test_rated
from 
	users
inner join user_roles on (user_roles.user_id = users.id)
where 
	users.deleted_at is null 
	AND username not like '%test-correct%'
	AND user_roles.role_id = 1
	AND users.demo = 0


- all active users with date restriction (! you need to change school_years.year and users.created_at TWICE in order to get the current results)
select 
    school_locations.customer_code,
    school_locations.name,
    t.teacher_count,
    s.students
from
    school_locations
    inner join 
(
select 
    count(users.id) as teacher_count, 
    school_locations.id
from
    users
    inner join school_locations on (school_locations.id = users.school_location_id)
where
    users.id IN (
        select distinct(user_id)  as user_id from teachers where deleted_at is null AND class_id in (
            select 
                distinct(school_classes.id) as school_classes_id
            from
                school_classes
                inner join school_years on (school_classes.`school_year_id` = school_years.id)
            where
                school_classes.deleted_at is null AND
                school_classes.demo = 0 AND
                school_years.year = 2019 AND
                school_years.deleted_at is null
				
        )
    ) AND
    users.deleted_at is null AND
    users.demo = 0 AND
    users.username not like '%test-correct.nl' AND
    users.created_at <= '2020-03-21'
group by 
    school_locations.customer_code,
    school_locations.name,
    school_locations.id
) t on t.id = school_locations.id
inner join (
select 
  count(users.id) as students, 
  school_locations.id,
  school_locations.customer_code,
  school_locations.name
from
  users
  inner join school_locations on (school_locations.id = users.school_location_id)
where
  users.id IN (
    select distinct(user_id)  as user_id from students where deleted_at is null AND class_id in (
      select 
        distinct(school_classes.id) as school_classes_id
      from
        school_classes
        inner join school_years on (school_classes.`school_year_id` = school_years.id)
      where
        school_classes.deleted_at is null AND
        school_classes.demo = 0 AND 
        school_years.year = 2019 AND
        school_years.deleted_at is null
        
    )
  ) AND
  users.deleted_at is null AND
  users.demo = 0 AND
  users.username not like '%test-correct.nl' AND
      users.created_at <= '2020-03-21'
group by 
  school_locations.customer_code,
  school_locations.name,
  school_locations.id
) s on (s.id = school_locations.id)

- weergave in toetsenbank

Docent mag geen toetsen van andere school zien

Docent mag geen demovak toetsen van andere docenten zien.

Onder eigen toetsen wordt bedoeld: Toetsen die ooit door de docent zijn aangemaakt, dus ook degene die horen bij een vak dat meer gegeven wordt.

Een docent heeft ‘toegang’ tot vakken die en een sectie delen.

Het “eigendom” van een toets door een schoollocatie wordt bepaald door de sectie van het vak waar de toets toe behoort

Docent zit in 1 schoollocatie en is geen onderdeel van een gedeelde sectie:

Docent mag eigen toetsen (ook met vakken die op dit moment niet gegeven ) zien en die van sectiegenoten.

Docent mag geen toetsen zien van sectiegenoten die vallen onder een andere sectie

Docent mag geen toetsen zien van een andere schoollocatie (= toetsen die een subject hebben die vallen onder een sectie van een andere schoollocatie)

Docent zit in 2 schoollocaties en is geen onderdeel van een gedeelde sectie:

Docent mag eigen toetsen (van de actieve schoollocatie) zien en die van sectiegenoten (van de actieve schoollocatie)

Docent mag eigen toetsen van de andere schoollocatie zien van vakken die in de actieve schoollocatie gegeven wordt

Docent mag geen toetsen zien van sectiegenoten die vallen onder een andere sectie

Docent mag geen toetsen zien van een andere schoollocatie, behalve die van hem/haarzelf

Docent zit in 1 schoollocatie en is onderdeel van een gedeelde sectie:

Docent mag eigen toetsen zien en die van sectiegenoten.

Docent mag geen toetsen zien van sectiegenoten die vallen onder een andere sectie

Docent mag toetsen zien van een andere schoollocatie als die vallen onder een gedeelde sectie en als de docent valt onder deze sectie

Docent zit in 2 schoollocaties en is onderdeel van een gedeelde sectie:

Docent mag eigen toetsen (van de actieve schoollocatie) zien en die van sectiegenoten (van de actieve schoollocatie)

Docent mag eigen toetsen van de andere schoollocatie zien van vakken die in de actieve schoollocatie gegeven wordt

Docent mag geen toetsen zien van sectiegenoten die vallen onder een andere sectie

Docent mag toetsen zien van een andere schoollocatie als die vallen onder een gedeelde sectie en als de docent valt onder deze sectie

