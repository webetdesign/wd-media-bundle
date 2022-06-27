<?php


namespace WebEtDesign\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity
 * @ORM\Table(name="wd_media__media")
 *
 * @Vich\Uploadable()
 * @ORM\EntityListeners({"WebEtDesign\MediaBundle\Listener\MediaListener"})
 */
class Media
{

    use TimestampableEntity;

    /**
     * @var int|null $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"media"})
     *
     */
    protected ?int $id = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Groups({"media"})
     */
    private string $label = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     * @Groups({"media"})
     */
    private ?string $category = null;

    /**
     * @var string|null
     * @Groups({"media"})
     */
    private ?string $categoryLabel = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     * @Groups({"media"})
     */
    private ?string $fileName = null;

    /**
     * @Vich\UploadableField(mapping="wd_media", fileNameProperty="fileName")
     * @var File|null
     */
    private ?File $file = null;


    /**
     * @var string|null
     * @ORM\Column(type="string")
     * @Groups({"media"})
     */
    private ?string $mimeType = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     * @Groups({"media"})
     */
    private ?string $extension = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"media"})
     */
    private ?string $cropData = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"media"})
     */
    private ?string $description = null;

    /**
     * @var null|string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"media"})
     */
    private ?string $permalink = '';

    /**
     * @var null|string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"media"})
     */
    protected ?string $alt = null;

    public function __toString()
    {
        return $this->getLabel();
    }

    public function isPicture(): bool
    {
        return in_array($this->getMimeType(), [
            'image/png',
            'image/jpeg',
            'image/bmp',
            'image/webp'
        ]);
    }

    public function isGif(): bool
    {
        return $this->getMimeType() === 'image/gif';
    }

    public function isSvg(): bool
    {
        return in_array($this->getMimeType(), [
            'image/svg+xml',
            'application/svg+xml',
        ]);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Media
     */
    public function setLabel(string $label): Media
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string|null $category
     * @return Media
     */
    public function setCategory(?string $category): Media
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return Media
     */
    public function setFileName(?string $fileName): Media
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     * @return Media
     */
    public function setFile(?File $file): Media
    {
        $this->file = $file;

        if ($file) {
            $this->setUpdatedAt(new \DateTime());
            $this->setCropData(null);
        }

        return $this;
    }

    /**
     * @param string|null $mimeType
     * @return Media
     */
    public function setMimeType(?string $mimeType): Media
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string|null $categoryLabel
     * @return Media
     */
    public function setCategoryLabel(?string $categoryLabel): Media
    {
        $this->categoryLabel = $categoryLabel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategoryLabel(): ?string
    {
        return $this->categoryLabel;
    }

    /**
     * @param string|null $extension
     * @return Media
     */
    public function setExtension(?string $extension): Media
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        if (!$this->extension && $this->mimeType){
            return strrev(substr(strrev($this->mimeType), 0,strpos(strrev($this->mimeType), '/')));
        }

        return $this->extension;
    }

    /**
     * @return string|null
     */
    public function getCropData(): ?string
    {
        return $this->cropData;
    }

    /**
     * @return array|null
     */
    public function getCropDataForFormatDevice($format, $device): ?array
    {
        $crop = json_decode($this->getCropData() ? $this->getCropData() : '[]', true);

        return isset($crop[$format]) ? $crop[$format][$device] ?? null : null;
    }

    /**
     * @param string|null $cropData
     * @return Media
     */
    public function setCropData(?string $cropData): Media
    {
        $this->cropData = $cropData;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Media
     */
    public function setDescription(?string $description): Media
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPermalink(): ?string
    {
        return $this->permalink;
    }

    /**
     * @param null|string $permalink
     * @return Media
     */
    public function setPermalink(?string $permalink): Media
    {
        $this->permalink = $permalink;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlt(): ?string
    {
        return !empty($this->alt) ? $this->alt : $this->getLabel();
    }

    /**
     * @param string|null $alt
     * @return Media
     */
    public function setAlt(?string $alt): Media
    {
        $this->alt = $alt;
        return $this;
    }

}
