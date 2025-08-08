<?php

namespace App\Entity;

use App\Enum\LeaveType;
use App\Enum\RequestStatus;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LeaveRepository;

#[ORM\Entity(repositoryClass: LeaveRepository::class)]
#[ORM\Table(name: 'leave')]
class Leave
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'datetime', name: 'start_date')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'datetime', name: 'end_date')]
    private \DateTimeInterface $endDate;

    #[ORM\Column(type: 'string', enumType: LeaveType::class, name: 'leave_type')]
    private LeaveType $leaveType;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: 'string', enumType: RequestStatus::class)]
    private RequestStatus $status;

    #[ORM\Column(type: 'datetime', name: 'leave_request_date')]
    private \DateTimeInterface $requestDate;

    #[ORM\Column(type: 'datetime', name: 'process_date', nullable: true)]
    private ?\DateTimeInterface $processDate = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $processedBy = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $proof = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $refusalReason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getLeaveType(): LeaveType
    {
        return $this->leaveType;
    }

    public function setLeaveType(LeaveType $leaveType): self
    {
        $this->leaveType = $leaveType;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getStatus(): RequestStatus
    {
        return $this->status;
    }

    public function setStatus(RequestStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getRequestDate(): \DateTimeInterface
    {
        return $this->requestDate;
    }

    public function setRequestDate(\DateTimeInterface $requestDate): self
    {
        $this->requestDate = $requestDate;
        return $this;
    }

    public function getProcessDate(): ?\DateTimeInterface
    {
        return $this->processDate;
    }

    public function setProcessDate(?\DateTimeInterface $processDate): self
    {
        $this->processDate = $processDate;
        return $this;
    }

    public function getProcessedBy(): ?string
    {
        return $this->processedBy;
    }

    public function setProcessedBy(?string $processedBy): self
    {
        $this->processedBy = $processedBy;
        return $this;
    }

    public function getProof(): ?string
    {
        return $this->proof;
    }

    public function setProof(?string $proof): self
    {
        $this->proof = $proof;
        return $this;
    }

    public function getRefusalReason(): ?string
    {
        return $this->refusalReason;
    }

    public function setRefusalReason(?string $refusalReason): self
    {
        $this->refusalReason = $refusalReason;
        return $this;
    }
}
