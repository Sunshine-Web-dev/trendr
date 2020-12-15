<?php

/**
 * A very simple lorem ipsum generator class
 *
 * I wrote this because I needed it, and all the other ones I found had licenses that made them
 * incompatible. Fucking licenses.
 */
class BBG_Lorem_Ipsum {
	var $words = array();

	public function __construct() {
		$this->setup_words();
	}

	protected function setup_words() {
		$text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In a tellus at turpis vestibulum porttitor eu quis ligula. Proin metus justo, varius non iaculis at, pulvinar ut metus. Mauris consequat aliquam elementum. Pellentesque rhoncus lorem a erat ullamcorper at luctus quam porta. Sed porttitor massa gravida dolor accumsan bibendum. Aenean a magna vel urna bibendum suscipit et non diam. Suspendisse feugiat, sem vitae pellentesque pretium, ligula elit consectetur ante, eget luctus est tellus et mauris.  Ut pellentesque cursus ligula, vel laoreet tellus blandit at. Pellentesque a tortor arcu, eu commodo ante. Morbi et vehicula lorem. Vestibulum lobortis adipiscing malesuada. In at hendrerit velit. Integer et purus sem, sed dictum purus. Quisque vehicula diam sit amet odio sollicitudin malesuada. Nam dapibus mattis nunc, at sollicitudin lacus cursus non. Maecenas at semper nulla.  Nunc tempus neque sed nunc lobortis id ornare turpis sollicitudin. Sed est justo, gravida non interdum et, consectetur vitae neque. Fusce quis sem risus. Etiam sit amet orci iaculis massa lacinia eleifend at sed leo. Aliquam dapibus convallis diam, eget scelerisque lacus vestibulum rutrum. Quisque quis eros id odio vulputate fringilla at id dolor. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus a ante eget mauris mattis aliquam eu suscipit ligula.  Ut sodales, dui ac accumsan rutrum, sapien odio aliquam ipsum, nec dapibus ligula massa in purus. In ante felis, posuere non adipiscing vitae, lacinia vel sem. Proin in diam nec mi vestibulum commodo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nulla facilisi. Etiam ultricies ligula non ligula rhoncus lacinia. Suspendisse vitae velit eu lorem imperdiet scelerisque. Phasellus ultrices dapibus metus, sit amet mattis quam cursus ut. Aenean at eros sed lectus lacinia rutrum.  Nulla facilisi. Integer tempus iaculis porttitor. Nulla ut dui nisi. Phasellus adipiscing nulla sed sem tempor fermentum. Aliquam erat volutpat. Nulla pharetra pulvinar erat, a commodo mauris tristique venenatis. Sed sed nulla dolor. Phasellus dapibus tellus non orci adipiscing pharetra. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;  Vestibulum sagittis lectus ac felis blandit ac faucibus tortor congue. Integer justo nibh, imperdiet a aliquet a, congue ut nulla. Mauris libero libero, sagittis eget ullamcorper at, mollis in metus. In hac habitasse platea dictumst. Sed in ornare urna. Etiam sit amet orci libero. Quisque porttitor, libero vel lacinia pulvinar, sem erat ultricies magna, vel auctor sem nunc nec lacus. Etiam iaculis varius eros, et aliquet nibh semper at. Curabitur semper, arcu id dapibus pretium, ipsum sem volutpat velit, ac hendrerit augue nibh a dui. Nulla rhoncus massa est, quis ullamcorper justo. Aliquam erat volutpat. Vestibulum quis lorem dolor. Mauris gravida eros pharetra lorem fringilla vitae hendrerit nisi laoreet. Integer consectetur, arcu a tempus imperdiet, nulla leo dignissim mi, ac pellentesque mi nisi id enim. In sit amet sapien ac metus molestie pellentesque.  Proin auctor, sem a molestie mattis, nibh nisi accumsan velit, non molestie velit lectus et velit. Vestibulum adipiscing pretium dolor quis faucibus. Aliquam dictum interdum diam vel varius. Maecenas pellentesque lorem ac nibh ullamcorper id dapibus tortor consectetur. Donec in pulvinar nisl. Vestibulum mattis massa ut nunc aliquet facilisis. Aenean erat mauris, pharetra at luctus venenatis, malesuada ac est. Nullam nec ante purus, in pretium turpis. Nulla viverra dui odio. Aliquam sem leo, venenatis vitae feugiat id, interdum a sapien.  Duis porttitor molestie turpis eu convallis. Aliquam erat volutpat. Aliquam vel orci sapien, at lobortis lorem. Suspendisse eleifend placerat fermentum. Suspendisse mattis suscipit turpis, ut sollicitudin nibh commodo sed. Vivamus quis mi in velit tristique sollicitudin. In vitae eros arcu, quis mollis urna.  Phasellus ultricies viverra purus, ut luctus leo commodo vitae. Suspendisse potenti. Duis consequat tortor vitae dolor sagittis vel dictum massa sollicitudin. Suspendisse dictum pharetra leo quis rhoncus. Phasellus sollicitudin elit in mauris lacinia quis elementum sapien scelerisque. Integer et ipsum velit, ac consectetur justo. Mauris sem quam, faucibus quis ultricies id, suscipit vel sem. Vivamus libero nibh, eleifend pulvinar fermentum ut, ultrices ac felis. Nulla a purus purus. Pellentesque luctus, justo nec fringilla euismod, sapien magna vehicula dui, vel tristique ligula quam sit amet ligula. Donec in felis id urna consequat pellentesque ut a arcu. Proin in magna ut lorem fringilla malesuada vitae sit amet magna.  Phasellus in nulla a ligula porttitor lobortis. Duis tincidunt turpis a mi sodales aliquam. Sed eu venenatis velit. Aenean non feugiat sem. Maecenas vulputate tempus porta. Sed vel mauris purus. Duis at nibh nunc, sit amet vulputate lorem.  Integer consectetur tincidunt semper. Aliquam vel elit eros, at sagittis turpis. Etiam lacus eros, placerat ut scelerisque eget, vestibulum at mi. Quisque a dolor ante, nec gravida nisl. Nam mattis nulla at enim eleifend aliquam. Pellentesque non sem eros, in congue mauris. Integer facilisis dolor et nibh molestie dictum. Aliquam pellentesque euismod dui nec dapibus. Donec id velit leo. Phasellus ut eros nisi.  Nulla risus tellus, placerat non malesuada id, dignissim quis elit. Nunc consectetur rutrum sem vitae blandit. Fusce luctus fringilla ligula, quis dictum turpis faucibus in. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras pretium, dui a blandit imperdiet, velit eros convallis nisi, vel porta velit ante vitae tellus. Cras condimentum diam in quam luctus vel pretium neque tincidunt. Proin facilisis volutpat lacus eleifend ultricies. Etiam viverra velit vitae velit ultrices tempus non ut augue.';

		$text_a = explode( ' ', $text );

		foreach( $text_a as $word ) {
			$word = str_replace( ',', '', $word );
			$word = str_replace( '.', '', $word );
			$word = strtolower( trim( $word ) );

			if ( !in_array( $word, $this->words ) ) {
				$this->words[] = $word;
			}
		}
	}

	public function generate( $number_of_words = 5 ) {

		$string_a = array();
		for( $i = 1; $i <= $number_of_words; $i++ ) {
			$key = array_rand( $this->words );
			$string_a[] = $this->words[$key];
		}

		$string = implode( ' ', $string_a );

		return $string;

	}
}

?>