INSERT INTO gen_doublons (title, type, lexTermElement,lexTermElement_e,aspect)
SELECT 
            c.lib
			, c.type
            , v.prefix, v.elision, v.id_conj
        FROM
            gen_concepts c
            inner join gen_concepts_verbes cv on cv.id_concept = c.id_concept
            inner join gen_verbes v on v.id_verbe = cv.id_verbe
