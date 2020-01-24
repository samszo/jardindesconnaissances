INSERT INTO gen_doublons (title, type, lexTermElement,lexTermElement_e,pluralNumberForm_f,pluralNumberForm_m, singularNumberForm_f, singularNumberForm_m)
SELECT 
            c.lib
            , c.type
            , a.prefix, a.elision, a.f_p, a.m_p, a.f_s, a.m_s
        FROM
            gen_concepts c
            inner join gen_concepts_adjectifs ca on ca.id_concept = c.id_concept
            inner join gen_adjectifs a on a.id_adj = ca.id_adj
