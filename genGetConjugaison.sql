SELECT 
            COUNT(*) nb,
            GROUP_CONCAT(DISTINCT d.aspect) aspects,
            c.num,
            c.modele,
            t.num,
            t.lib
        FROM
            gen_doublons d
                INNER JOIN
            gen_conjugaisons c ON c.id_conj = d.aspect
                INNER JOIN
            gen_terminaisons t ON t.id_conj = c.id_conj
		WHERE c.modele = 'pleuvoir'
        GROUP BY c.modele, t.num , t.lib
        ORDER BY c.modele, t.num 