SELECT 
            COUNT(*) nb, c.lib
            , g.valeur
            , a.prefix ap, a.elision ae, a.m_s, a.m_p, a.f_p
            , s.prefix sp, s.elision se, s.genre, s.s, s.p
            , sy.num, sy.ordre, sy.lib sylib
            , v.prefix vp, v.id_conj, v.elision ve
        FROM
            gen_concepts c
            left join gen_concepts_generateurs cg on cg.id_concept = c.id_concept
            left join gen_generateurs g on g.id_gen = cg.id_gen
            left join gen_concepts_adjectifs ca on ca.id_concept = c.id_concept
            left join gen_adjectifs a on a.id_adj = ca.id_adj
            left join gen_concepts_substantifs cs on cs.id_concept = c.id_concept
            left join gen_substantifs s on s.id_sub = cs.id_sub
            left join gen_concepts_syntagmes csy on csy.id_concept = c.id_concept
            left join gen_syntagmes sy on sy.id_syn = csy.id_syn
            left join gen_concepts_verbes cv on cv.id_concept = c.id_concept
            left join gen_verbes v on v.id_verbe = cv.id_verbe
-- WHERE c.lib = "abandon"
        GROUP BY c.lib -- , g.valeur, a.prefix, s.prefix, sy.lib, v.prefix
        ORDER BY c.lib DESC
