<?php
/**
 * Seeder idempotente de termos canônicos do Portal A12.
 *
 * Executa na hook 'init' com prioridade 999 (após registro de todas as taxonomias).
 * Seguro para executar em múltiplos requests — term_exists() evita duplicatas.
 *
 * Termos semeados:
 *   channel        → 12 núcleos editoriais         (EDITORIAL_TAXONOMY.md §2.1)
 *   editorial_type → 8 tipos editoriais             (EDITORIAL_TAXONOMY.md §4)
 *   page_type      → 10 tipos de página estrutural  (EDITORIAL_TAXONOMY.md §6.4)
 *   content_format → 9 formatos de apresentação     (EDITORIAL_TAXONOMY.md §6.6)
 *   category       → 58 categorias editoriais únicas (EDITORIAL_TAXONOMY.md §3.1)
 */

defined( 'ABSPATH' ) || exit;

class A12_TermSeeder {

	public static function init(): void {
		add_action( 'init', [ self::class, 'seed' ], 999 );
	}

	public static function seed(): void {
		// Evita erros durante wp core install (tabelas ainda não existem).
		if ( ! is_blog_installed() ) {
			return;
		}

		self::seed_terms( 'channel',        self::channels() );
		self::seed_terms( 'editorial_type', self::editorial_types() );
		self::seed_terms( 'page_type',      self::page_types() );
		self::seed_terms( 'content_format', self::content_formats() );
		self::seed_categories();
	}

	// ------------------------------------------------------------------
	// Internal
	// ------------------------------------------------------------------

	private static function seed_terms( string $taxonomy, array $terms ): void {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}
		foreach ( $terms as $slug => $name ) {
			if ( ! term_exists( $slug, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
			}
		}
	}

	private static function seed_categories(): void {
		if ( ! taxonomy_exists( 'category' ) ) {
			return;
		}
		foreach ( self::categories() as $name ) {
			$slug = sanitize_title( $name );
			if ( ! term_exists( $slug, 'category' ) ) {
				wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
			}
		}
	}

	// ------------------------------------------------------------------
	// Vocabulary
	// ------------------------------------------------------------------

	/**
	 * 12 núcleos canônicos — EDITORIAL_TAXONOMY.md §2.1.
	 * Slugs exatos conforme vocab_channels.slug no Supabase.
	 */
	private static function channels(): array {
		return [
			'tv'                => 'TV Aparecida',
			'redacaoa12'        => 'Redação A12',
			'radio'             => 'Rádio Aparecida',
			'santuario'         => 'Santuário Nacional',
			'redentoristas'     => 'Redentoristas',
			'radio-pop'         => 'Rádio Pop',
			'familiadosdevotos' => 'Família dos Devotos',
			'jovens-de-maria'   => 'Jovens de Maria',
			'academia-marial'   => 'Academia Marial',
			'jornal-santuario'  => 'Jornal Santuário',
			'devotos-mirins'    => 'Devotos Mirins',
			'centro-de-eventos' => 'Centro de Eventos',
		];
	}

	/**
	 * 8 tipos editoriais — EDITORIAL_TAXONOMY.md §4.
	 */
	private static function editorial_types(): array {
		return [
			'noticias'    => 'Notícias',
			'entrevistas' => 'Entrevistas',
			'coberturas'  => 'Coberturas',
			'servicos'    => 'Serviços',
			'reflexoes'   => 'Reflexões',
			'comunicados' => 'Comunicados',
			'testemunhos' => 'Testemunhos',
			'campanhas'   => 'Campanhas',
		];
	}

	/**
	 * 10 tipos de página estrutural — EDITORIAL_TAXONOMY.md §6.4.
	 */
	private static function page_types(): array {
		return [
			'institucional'          => 'Institucional',
			'servico'                => 'Serviço',
			'hub'                    => 'Hub',
			'formulario'             => 'Formulário',
			'contato'                => 'Contato',
			'legal'                  => 'Legal',
			'sala-de-imprensa'       => 'Sala de Imprensa',
			'experiencia-devocional' => 'Experiência Devocional',
			'aplicacao'              => 'Aplicação',
			'acervo'                 => 'Acervo',
		];
	}

	/**
	 * 9 formatos de apresentação de conteúdo — EDITORIAL_TAXONOMY.md §6.6.
	 */
	private static function content_formats(): array {
		return [
			'texto'       => 'Texto',
			'formulario'  => 'Formulário',
			'galeria'     => 'Galeria',
			'infografico' => 'Infográfico',
			'video'       => 'Vídeo',
			'audio'       => 'Áudio',
			'calendario'  => 'Calendário',
			'interativo'  => 'Interativo',
			'app-externo' => 'App Externo',
		];
	}

	/**
	 * 58 categorias editoriais únicas — EDITORIAL_TAXONOMY.md §3.1.
	 * Slugs gerados automaticamente via sanitize_title().
	 * Exemplos: 'Ação Social' → 'acao-social', 'Fé' → 'fe'.
	 *
	 * Organizadas por primeiro núcleo em que aparecem.
	 * Categorias compartilhadas entre núcleos são semeadas apenas uma vez.
	 */
	private static function categories(): array {
		return [
			// TV Aparecida
			'Culinária', 'Artesanato', 'Fé', 'Devoção', 'Cultura', 'Entretenimento',
			'Família', 'Comportamento', 'Sociedade',

			// Redação A12 (adicionais)
			'Igreja', 'Vida Cristã', 'Cidadania', 'Espiritualidade', 'Histórias de Fé',

			// Santuário Nacional (adicionais)
			'Liturgia', 'Romaria', 'Serviços ao Romeiro', 'História', 'Memória',
			'Caridade', 'Ação Social', 'Agenda',

			// Rádio Aparecida (adicionais)
			'Saúde', 'Regional',

			// Rádio Pop (adicionais)
			'Música', 'Comunidade', 'Gastronomia', 'Eventos', 'Promoções',

			// Redentoristas (adicionais)
			'Missão Redentorista', 'Vocação', 'Formação', 'Espiritualidade Redentorista',

			// Família dos Devotos (adicionais)
			'Celebrações', 'Relatos de Fé', 'Santuário', 'Evangelização',

			// Devotos Mirins (adicionais)
			'Aprendizado', 'Catequese', 'Atividades', 'Jogos', 'Natureza',

			// Jovens de Maria (adicionais)
			'Movimentos Juvenis', 'Devoção Mariana',

			// Academia Marial (adicionais)
			'Mariologia', 'Doutrina', 'Espiritualidade Mariana', 'Pesquisa', 'Figuras da Fé',

			// Jornal Santuário (adicionais)
			'Direitos', 'Educação', 'Economia', 'Desenvolvimento', 'Pastoral',

			// Centro de Eventos (adicionais)
			'Romarias', 'Esporte', 'Estrutura', 'Infraestrutura',
		];
	}
}
