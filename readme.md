## test-correct Laravel backend.

- #select count(*) as aantal, DATE_FORMAT(created_at,'%Y-%m') as maand from questions GROUP BY DATE_FORMAT(created_at, '%Y-%m') order by maand #vraag items groei
- #select count(*) from users where updated_at >= '2019-01-01' AND users.id IN (select user_id from teachers) # aantal ingelogde docenten accounts sinds 1 januari 2019
- #select count(*) from users where deleted_at is null AND users.id IN (select user_id from teachers) # totaal aantal actieve docenten accounts
- #select count(*) from users where updated_at >= '2019-01-01' AND users.id IN (select user_id from students) # aantal ingelogde studenten accounts sinds 1 januari 2019
- #select count(*) from users where deleted_at is null AND users.id IN (select user_id from students) # totaal aantal actieve studenten accounts
- #select count(*) as aantal, DATE_FORMAT(time_start,'%Y-%m') as maand from test_takes GROUP BY DATE_FORMAT(time_start, '%Y-%m') order by maand #toets afname per maand
- #select sum(aantal) as total, maand from (select count(*) as aantal, DATE_FORMAT(updated_at,'%Y-%m') as maand from test_participants where heartbeat_at is not null GROUP BY DATE_FORMAT(updated_at, '%Y-%m'), user_id order by maand) as t group by maand order by maand #toets deelname per maand
- #select sum(aantal) as totaal, vak from (select count(*) as aantal, subjects.`name` as vak from questions left join subjects on subjects.id = questions.subject_id group by subject_id order by subjects.name) as t group by vak order by vak # vraagitems per vak
- #select count(*) as aantal, subjects.name as vak from test_takes inner join tests on tests.id = test_takes.test_id inner join subjects on subjects.id = tests.subject_id group by subject_id order by vak) as t group by vak order by vak # toetsafnames per vak
- #select count(*) as aantal from (select count(*) as aantal from test_questions group by question_id) as t where t.aantal >=1 # vraagitems minimaal 1x gebruikt in toets

