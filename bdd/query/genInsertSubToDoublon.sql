INSERT INTO gen_doublons (title, type, lexTermElement, lexTermElement_e,grammaticalGender,singularNumberForm, pluralNumberForm)
SELECT 
            c.lib
            , c.type
            , s.prefix, s.elision, s.genre, s.s, s.p
        FROM
            gen_concepts c
            left join gen_concepts_substantifs cs on cs.id_concept = c.id_concept
            left join gen_substantifs s on s.id_sub = cs.id_sub
		
