<?
class EbookSource{
	public ?int $EbookSourceId = null;
	public ?int $EbookId = null;
	public EbookSourceType $Type;
	public string $Url;

	public static function FromTypeAndUrl(EbookSourceType $type, string $url): EbookSource{
		$instance = new EbookSource();
		$instance->Type = $type;
		$instance->Url = $url;
		return $instance;
	}

	public function Create(): void{
		Db::Query('
			INSERT into EbookSources (EbookId, Type, Url)
			values (?,
				?,
				?)
		', [$this->EbookId, $this->Type, $this->Url]);

		$this->EbookSourceId = Db::GetLastInsertedId();
	}
}
