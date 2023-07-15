INSERT INTO gen_doublons (title, type, lexTermElement, description)
SELECT 
            c.lib
            , c.type
            , sy.lib, concat('#',sy.num)
        FROM
            gen_concepts c
            inner join gen_concepts_syntagmes csy on csy.id_concept = c.id_concept
            inner join gen_syntagmes sy on sy.id_syn = csy.id_syn
            where sy.num <> -1
