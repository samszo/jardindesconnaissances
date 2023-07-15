SELECT u.doc_id, u.titre, u.url, u.type
			 , uc.url ucurl
			 , up.url upurl
			FROM flux_doc u
			  INNER JOIN flux_doc uc ON uc.tronc = u.doc_id AND uc.type = 39
			  INNER JOIN flux_doc up ON up.tronc = u.doc_id AND up.type = 2