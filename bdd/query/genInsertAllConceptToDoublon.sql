INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, lexTermElement,lexTermElement_e,pluralNumberForm_f,pluralNumberForm_m, singularNumberForm_f, singularNumberForm_m)
SELECT
			concat(c.id_dico,'_',c.id_concept)
			,concat(a.id_dico,'_',a.id_adj)
            , c.lib
            , c.type
            , 'adjectif'
            , a.prefix, a.elision, a.f_p, a.m_p, a.f_s, a.m_s
        FROM
            gen_concepts c
            inner join gen_concepts_adjectifs ca on ca.id_concept = c.id_concept
            inner join gen_adjectifs a on a.id_adj = ca.id_adj;
INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, lexTermElement,lexTermElement_e,aspect)
SELECT 
			concat(c.id_dico,'_',c.id_concept)
			,concat(v.id_dico,'_',v.id_verbe)
            , c.lib
			, c.type
            , 'verbe'
            , v.prefix, v.elision, v.id_conj
        FROM
            gen_concepts c
            inner join gen_concepts_verbes cv on cv.id_concept = c.id_concept
            inner join gen_verbes v on v.id_verbe = cv.id_verbe
		WHERE v.elision IN (0, 1);
            
INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, lexTermElement, lexTermElement_e,grammaticalGender,singularNumberForm, pluralNumberForm)
SELECT 
			concat(c.id_dico,'_',c.id_concept)
			,concat(s.id_dico,'_',s.id_sub)
            , c.lib
            , c.type
            , 'substantif'
            , s.prefix, s.elision, s.genre, s.s, s.p
        FROM
            gen_concepts c
            inner join gen_concepts_substantifs cs on cs.id_concept = c.id_concept
            inner join gen_substantifs s on s.id_sub = cs.id_sub;

            
INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, lexTermElement, description)
SELECT 
			concat(c.id_dico,'_',c.id_concept)
			,concat(sy.id_dico,'_',sy.id_syn)
            , c.lib
            , c.type
            , 'syntagme'
            , sy.lib, concat('#',sy.num)
        FROM
            gen_concepts c
            inner join gen_concepts_syntagmes csy on csy.id_concept = c.id_concept
            inner join gen_syntagmes sy on sy.id_syn = csy.id_syn
            where sy.num <> -1;

INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, description)
SELECT 
			concat(c.id_dico,'_',c.id_concept)
			,concat(g.id_dico,'_',g.id_gen)
            , c.lib
            , c.type
            , 'generateur'
            , g.valeur
        FROM
            gen_concepts c
            inner join gen_concepts_generateurs cg on cg.id_concept = c.id_concept
            inner join gen_generateurs g on g.id_gen = cg.id_gen;
            
INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, lexTermElement,lexTermElement_e,description)
SELECT 
			concat(p.id_dico,'_pronom')
			,concat(p.id_dico,'_',p.id_pronom)
            ,'pronoms français'
            , REPLACE(p.type,'é','e')
            , 'pronom'
            , p.lib, p.lib_eli, concat(REPLACE(p.type,'é','e'),p.num)
        FROM
            gen_pronoms p;

INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, lexTermElement,description)
SELECT 
			concat(n.id_dico,'_negation')
			,concat(n.id_dico,'_',n.id_negation)
            , 'négations françaises'
            , 'negation'
            , 'negation'
            , n.lib, concat('negation',n.num)
        FROM
            gen_negations n;


INSERT INTO gen_doublons (refC, refL, title, type_concept, type_lien, description
	, singularNumberForm_m, singularNumberForm_f, singularNumberForm_me, singularNumberForm_fe
    , pluralNumberForm_m, pluralNumberForm_f, pluralNumberForm_me, pluralNumberForm_fe)
SELECT
	concat(d.id_dico,'_dtm')
	,concat(d.id_dico,'_',d.id_dtm)
	,'déterminants français', 'determinant', 'determinant',
    concat('determinant',d.num)
    , d0.lib lib0, d1.lib lib1, d2.lib lib2, d3.lib lib3
    , d4.lib lib4, d5.lib lib5, d6.lib lib6, d7.lib lib7
FROM
    gen_determinants d
        INNER JOIN
    gen_determinants d0 ON d0.num = d.num
        AND d0.id_dico = d.id_dico
        AND d0.ordre = 0
        INNER JOIN
    gen_determinants d1 ON d1.num = d.num
        AND d1.id_dico = d.id_dico
        AND d1.ordre = 1
        INNER JOIN
    gen_determinants d2 ON d2.num = d.num
        AND d2.id_dico = d.id_dico
        AND d2.ordre = 2
        INNER JOIN
    gen_determinants d3 ON d3.num = d.num
        AND d3.id_dico = d.id_dico
        AND d3.ordre = 3
        INNER JOIN
    gen_determinants d4 ON d4.num = d.num
        AND d4.id_dico = d.id_dico
        AND d4.ordre = 4
        INNER JOIN
    gen_determinants d5 ON d5.num = d.num
        AND d5.id_dico = d.id_dico
        AND d5.ordre = 5
        INNER JOIN
    gen_determinants d6 ON d6.num = d.num
        AND d6.id_dico = d.id_dico
        AND d6.ordre = 6
        INNER JOIN
    gen_determinants d7 ON d7.num = d.num
        AND d7.id_dico = d.id_dico
        AND d7.ordre = 7
WHERE
    d.id_dico = 46
GROUP BY d.num

		

