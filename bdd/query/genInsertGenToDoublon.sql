 INSERT INTO gen_doublons (title, type, description)
SELECT 
            c.lib
            , c.type
            , g.valeur
        FROM
            gen_concepts c
            inner join gen_concepts_generateurs cg on cg.id_concept = c.id_concept
            inner join gen_generateurs g on g.id_gen = cg.id_gen
